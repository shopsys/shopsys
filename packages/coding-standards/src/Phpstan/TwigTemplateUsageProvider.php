<?php

declare(strict_types=1);

namespace Shopsys\CodingStandards\Phpstan;

use Override;
use ReflectionClassConstant;
use ReflectionMethod;
use ReflectionProperty;
use ShipMonk\PHPStan\DeadCode\Provider\ReflectionBasedMemberUsageProvider;
use ShipMonk\PHPStan\DeadCode\Provider\VirtualUsageData;

class TwigTemplateUsageProvider extends ReflectionBasedMemberUsageProvider
{
    use ClassMemberReferenceCollectorTrait;
    use FileFinderTrait;

    protected const array TWIG_ACCESSOR_PREFIXES = ['get', 'is', 'has'];

    protected const array CLASS_MEMBER_PATTERNS = [
        '~constant\(\s*[\'"]([A-Za-z_][\w\\\\]*+)::([a-zA-Z_]\w*)[\'"]~',
        '~controller\(\s*[\'"]([A-Za-z_][\w\\\\]*+)::([a-zA-Z_]\w*)[\'"]~',
    ];

    /**
     * @var array<string, true>|null
     */
    protected ?array $accessedNames = null;

    /**
     * @var array<string, true>
     */
    protected array $writtenComponentPropNames = [];

    /**
     * @param string[] $templateDirectoryPaths
     */
    public function __construct(
        protected readonly array $templateDirectoryPaths,
    ) {
    }

    #[Override]
    protected function shouldMarkMethodAsUsed(ReflectionMethod $method): ?VirtualUsageData
    {
        $this->initializeReferences();

        if ($this->isMemberReferenced($method->getDeclaringClass()->getName(), $method->getName())) {
            return VirtualUsageData::withNote('Method is referenced in a Twig template');
        }

        if (!$method->isPublic()) {
            return null;
        }

        if ($this->isAccessedFromTwig($method->getName())) {
            return VirtualUsageData::withNote('Method matches an object accessor used in a Twig template');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkConstantAsUsed(ReflectionClassConstant $constant): ?VirtualUsageData
    {
        $this->initializeReferences();

        if ($this->isMemberReferenced($constant->getDeclaringClass()->getName(), $constant->getName())) {
            return VirtualUsageData::withNote('Constant is referenced in a Twig template');
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

        if (isset($this->accessedNames[strtolower($property->getName())])) {
            return VirtualUsageData::withNote('Property matches an object accessor used in a Twig template');
        }

        return null;
    }

    #[Override]
    protected function shouldMarkPropertyAsWritten(ReflectionProperty $property): ?VirtualUsageData
    {
        $this->initializeReferences();

        if (!$property->isPublic()) {
            return null;
        }

        if (isset($this->writtenComponentPropNames[strtolower($property->getName())])) {
            return VirtualUsageData::withNote('Property matches a Twig component prop populated from a template');
        }

        return null;
    }

    protected function isAccessedFromTwig(string $methodName): bool
    {
        $normalizedMethodName = strtolower($methodName);

        if (isset($this->accessedNames[$normalizedMethodName])) {
            return true;
        }

        foreach (static::TWIG_ACCESSOR_PREFIXES as $accessorPrefix) {
            if (str_starts_with($normalizedMethodName, $accessorPrefix) && strlen($normalizedMethodName) > strlen($accessorPrefix)) {
                if (isset($this->accessedNames[substr($normalizedMethodName, strlen($accessorPrefix))])) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function initializeReferences(): void
    {
        if ($this->accessedNames !== null) {
            return;
        }

        $this->accessedNames = [];

        foreach ($this->findFilePathsBySuffix($this->templateDirectoryPaths, '.twig') as $twigFilePath) {
            $twigContent = file_get_contents($twigFilePath);

            if ($twigContent === false) {
                continue;
            }

            $this->collectAccessedNames($twigContent);
            $this->collectClassMemberReferences($twigContent, static::CLASS_MEMBER_PATTERNS);
            $this->collectWrittenComponentPropNames($twigContent);
        }
    }

    protected function collectAccessedNames(string $twigContent): void
    {
        if (preg_match_all('~\{\{(.+?)\}\}|\{%(.+?)%\}~s', $twigContent, $expressionMatches) === false) {
            return;
        }

        foreach ([...$expressionMatches[1], ...$expressionMatches[2]] as $twigExpression) {
            if (preg_match_all('~\.([a-zA-Z_]\w*)~', $twigExpression, $accessMatches) === false) {
                continue;
            }

            foreach ($accessMatches[1] as $accessedName) {
                $this->accessedNames[strtolower($accessedName)] = true;
            }
        }
    }

    protected function collectWrittenComponentPropNames(string $twigContent): void
    {
        if (preg_match_all('~component\s*\(\s*[\'"][^\'"]+[\'"]\s*,\s*\{(.*?)\}\s*\)~s', $twigContent, $componentPropsMatches) === false) {
            return;
        }

        foreach ($componentPropsMatches[1] as $componentProps) {
            if (preg_match_all('~([a-zA-Z_]\w*)\s*:~', $componentProps, $propNameMatches) === false) {
                continue;
            }

            foreach ($propNameMatches[1] as $propName) {
                $this->writtenComponentPropNames[strtolower($propName)] = true;
            }
        }
    }
}
