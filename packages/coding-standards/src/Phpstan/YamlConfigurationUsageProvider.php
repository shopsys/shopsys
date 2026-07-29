<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlConfigurationUsageProvider extends ReflectionBasedMemberUsageProvider
{
    use ClassMemberReferenceCollectorTrait;
    use FileFinderTrait;

    protected const array GETTER_PREFIXES = ['get', 'is', 'has'];

    protected const array CLASS_MEMBER_PATTERNS = [
        '~@([A-Za-z_][\w\\\\]*+):([a-zA-Z_]\w*)~',
        '~\[\s*"([A-Za-z_][\w\\\\]*+)"\s*,\s*"([a-zA-Z_]\w*)"\s*\]~',
        '~\[\s*\'([A-Za-z_][\w\\\\]*+)\'\s*,\s*\'([a-zA-Z_]\w*)\'\s*\]~',
        '~([A-Za-z_][\w\\\\]*+)::([a-zA-Z_]\w*)~',
    ];

    protected bool $referencesInitialized = false;

    /**
     * @var array<string, true>
     */
    protected array $referencedMethodNames = [];

    /**
     * @var array<string, true>
     */
    protected array $graphQlFieldNames = [];

    /**
     * @var array<string, true>
     */
    protected array $serviceClassNames = [];

    /**
     * @param string[] $configDirectoryPaths
     */
    public function __construct(
        protected readonly array $configDirectoryPaths,
    ) {
    }

    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        $this->initializeReferences();

        $className = $method->getDeclaringClass()->getName();
        $methodName = $method->getName();

        if ($this->isMemberReferenced($className, $methodName)) {
            return VirtualUsageData::withNote('Method is referenced in yaml configuration');
        }

        if ($methodName === '__construct' && isset($this->serviceClassNames[$className])) {
            return VirtualUsageData::withNote('Class is registered as a service in yaml configuration');
        }

        if (isset($this->referencedMethodNames[$methodName])) {
            return VirtualUsageData::withNote('Method name is referenced in yaml configuration');
        }

        if ($this->isGraphQlFieldAccessor($className, $methodName)) {
            return VirtualUsageData::withNote('Method matches a GraphQL type field resolved by the default field resolver');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkConstantAsUsed(ReflectionClassConstant $constant): ?VirtualUsageData
    {
        $this->initializeReferences();

        $className = $constant->getDeclaringClass()->getName();

        if ($this->isMemberReferenced($className, $constant->getName())) {
            return VirtualUsageData::withNote('Constant is referenced in yaml configuration');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkPropertyAsRead(ReflectionProperty $property): ?VirtualUsageData
    {
        $this->initializeReferences();

        if (!$property->isPublic()) {
            return null;
        }

        if (!str_contains($property->getDeclaringClass()->getName(), 'FrontendApi')) {
            return null;
        }

        if (isset($this->graphQlFieldNames[strtolower($property->getName())])) {
            return VirtualUsageData::withNote('Property matches a GraphQL type field resolved by the default field resolver');
        }

        return null;
    }

    protected function isGraphQlFieldAccessor(string $className, string $methodName): bool
    {
        if (!str_contains($className, 'FrontendApi')) {
            return false;
        }

        $normalizedMethodName = strtolower($methodName);
        $methodNameCandidates = [$normalizedMethodName];

        if (str_ends_with($normalizedMethodName, 'promise')) {
            $methodNameCandidates[] = substr($normalizedMethodName, 0, -strlen('promise'));
        }

        $fieldNameCandidates = $methodNameCandidates;

        foreach ($methodNameCandidates as $methodNameCandidate) {
            foreach (static::GETTER_PREFIXES as $getterPrefix) {
                if (str_starts_with($methodNameCandidate, $getterPrefix) && strlen($methodNameCandidate) > strlen($getterPrefix)) {
                    $fieldNameCandidates[] = substr($methodNameCandidate, strlen($getterPrefix));
                }
            }
        }

        foreach ($fieldNameCandidates as $fieldNameCandidate) {
            if (isset($this->graphQlFieldNames[$fieldNameCandidate])) {
                return true;
            }
        }

        return false;
    }

    protected function initializeReferences(): void
    {
        if ($this->referencesInitialized) {
            return;
        }

        $this->referencesInitialized = true;

        foreach ($this->findFilePathsBySuffix($this->configDirectoryPaths, '.yaml') as $yamlFilePath) {
            $yamlContent = file_get_contents($yamlFilePath);

            if ($yamlContent === false) {
                continue;
            }

            $this->collectClassMemberReferences($yamlContent, static::CLASS_MEMBER_PATTERNS);
            $this->collectServiceMethodReferences($yamlContent);
            $this->collectExpressionMethodCalls($yamlContent);
            $this->collectServiceClassNames($yamlFilePath);

            if (str_ends_with($yamlFilePath, '.types.yaml')) {
                $this->collectGraphQlFieldNames($yamlFilePath);
            }
        }
    }

    protected function collectServiceMethodReferences(string $yamlContent): void
    {
        $serviceMethodPatterns = [
            '~(?<![\w-])method:\s*[\'"]?([a-zA-Z_]\w*)~',
            '~[\'"][a-zA-Z_]\w*::([a-zA-Z_]\w*)[\'"]~',
        ];

        foreach ($serviceMethodPatterns as $serviceMethodPattern) {
            if (preg_match_all($serviceMethodPattern, $yamlContent, $matches) === false) {
                continue;
            }

            foreach ($matches[1] as $referencedMethodName) {
                if ($referencedMethodName === 'class') {
                    continue;
                }

                $this->referencedMethodNames[$referencedMethodName] = true;
            }
        }
    }

    protected function collectExpressionMethodCalls(string $yamlContent): void
    {
        if (preg_match_all('~^.*@=.*$~m', $yamlContent, $expressionLineMatches) === false) {
            return;
        }

        foreach ($expressionLineMatches[0] as $expressionLine) {
            if (preg_match_all('~\.([a-zA-Z_]\w*)\s*\(~', $expressionLine, $methodCallMatches) === false) {
                continue;
            }

            foreach ($methodCallMatches[1] as $calledMethodName) {
                $this->referencedMethodNames[$calledMethodName] = true;
            }
        }
    }

    protected function collectServiceClassNames(string $yamlFilePath): void
    {
        try {
            $parsedYaml = Yaml::parseFile($yamlFilePath, Yaml::PARSE_CUSTOM_TAGS);
        } catch (ParseException) {
            return;
        }

        if (!is_array($parsedYaml) || !isset($parsedYaml['services']) || !is_array($parsedYaml['services'])) {
            return;
        }

        foreach ($parsedYaml['services'] as $serviceId => $serviceDefinition) {
            if (!is_string($serviceId) || str_starts_with($serviceId, '_') || !str_contains($serviceId, '\\') || str_ends_with($serviceId, '\\')) {
                continue;
            }

            $this->serviceClassNames[ltrim($serviceId, '\\')] = true;

            if (is_array($serviceDefinition) && isset($serviceDefinition['class']) && is_string($serviceDefinition['class'])) {
                $this->serviceClassNames[ltrim($serviceDefinition['class'], '\\')] = true;
            }
        }
    }

    protected function collectGraphQlFieldNames(string $yamlFilePath): void
    {
        try {
            $parsedYaml = Yaml::parseFile($yamlFilePath, Yaml::PARSE_CUSTOM_TAGS);
        } catch (ParseException) {
            return;
        }

        if (!is_array($parsedYaml)) {
            return;
        }

        $this->collectFieldNamesRecursively($parsedYaml);
    }

    /**
     * @param array<array-key, mixed> $parsedYamlSection
     */
    protected function collectFieldNamesRecursively(array $parsedYamlSection): void
    {
        foreach ($parsedYamlSection as $yamlKey => $yamlValue) {
            if (in_array($yamlKey, ['fields', 'connectionFields', 'edgeFields'], true) && is_array($yamlValue)) {
                foreach (array_keys($yamlValue) as $fieldName) {
                    if (is_string($fieldName)) {
                        $this->graphQlFieldNames[strtolower($fieldName)] = true;
                    }
                }
            }

            if (is_array($yamlValue)) {
                $this->collectFieldNamesRecursively($yamlValue);
            }
        }
    }
}
