<?php

declare(strict_types=1);

namespace Symplify\EasyCodingStandard\Yaml;

use Nette\Utils\Strings;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

/**
 * Fix for https://github.com/symplify/symplify/issues/2872
 *
 * Before:
 *
 * services:
 *      # fixer
 *      ArrayFixer:
 *          syntax: short
 *      # sniff
 *      ArraySniff:
 *          syntax: short
 *
 * After:
 *
 * services:
 *      # fixer
 *      ArrayFixer:
 *          calls:
 *              - ['configure', [['syntax' => 'short']]
 *      # sniff
 *      ArraySniff:
 *          parameters:
 *              $syntax: 'short'
 */
final class CheckerServiceParametersShifter
{
    /**
     * @var string
     */
    private const SERVICES_KEY = 'services';

    /**
     * @var \Symplify\EasyCodingStandard\Yaml\CheckerConfigurationGuardian
     */
    private $checkerConfigurationGuardian;

    /**
     * @var \Symplify\PackageBuilder\Strings\StringFormatConverter
     */
    private $stringFormatConverter;

    private const SERVICE_KEYWORDS = [
        'alias',
        'parent',
        'class',
        'shared',
        'synthetic',
        'lazy',
        'public',
        'abstract',
        'deprecated',
        'factory',
        'file',
        'arguments',
        'properties',
        'configurator',
        'calls',
        'tags',
        'decorates',
        'decoration_inner_name',
        'decoration_priority',
        'decoration_on_invalid',
        'autowire',
        'autoconfigure',
        'bind',
    ];

    public function __construct()
    {
        $this->checkerConfigurationGuardian = new CheckerConfigurationGuardian();
        $this->stringFormatConverter = new StringFormatConverter();
    }

    /**
     * @param mixed[] $configuration
     * @return mixed[]
     */
    public function process(array $configuration): array
    {
        if (!isset($configuration[self::SERVICES_KEY]) || !is_array($configuration[self::SERVICES_KEY])) {
            return $configuration;
        }

        $configuration[self::SERVICES_KEY] = $this->processServices($configuration[self::SERVICES_KEY]);

        return $configuration;
    }

    /**
     * @param mixed[] $services
     * @return mixed[]
     */
    private function processServices(array $services): array
    {
        foreach ($services as $serviceName => $serviceDefinition) {
            if (!$this->isCheckerClass($serviceName) || empty($serviceDefinition)) {
                continue;
            }

            if (Strings::endsWith($serviceName, 'Fixer')) {
                $services = $this->processFixer($services, $serviceName, $serviceDefinition);
            }

            if (Strings::endsWith($serviceName, 'Sniff')) {
                $services = $this->processSniff($services, $serviceName, $serviceDefinition);
            }

            // cleanup parameters
            $services = $this->cleanupParameters($services, $serviceDefinition, $serviceName);
        }

        return $services;
    }

    /**
     * @param string $checker
     * @return bool
     */
    private function isCheckerClass(string $checker): bool
    {
        return Strings::endsWith($checker, 'Fixer') || Strings::endsWith($checker, 'Sniff');
    }

    /**
     * @param mixed[] $services
     * @param string $checker
     * @param mixed[] $serviceDefinition
     * @return mixed[]
     */
    private function processFixer(array $services, string $checker, array $serviceDefinition): array
    {
        $this->checkerConfigurationGuardian->ensureFixerIsConfigurable($checker, $serviceDefinition);

        foreach (array_keys($serviceDefinition) as $key) {
            if ($this->isReservedKey($key)) {
                continue;
            }

            $serviceDefinition = $this->correctHeader($checker, $serviceDefinition);
            $serviceDefinition = $this->stringFormatConverter->camelCaseToUnderscoreInArrayKeys($serviceDefinition);

            $services[$checker]['calls'] = [['configure', [$serviceDefinition]]];
        }

        return $services;
    }

    /**
     * @param mixed[] $services
     * @param string $checker
     * @param mixed[] $serviceDefinition
     * @return mixed[]
     */
    private function processSniff(array $services, string $checker, array $serviceDefinition): array
    {
        // move parameters to property setters
        foreach ($serviceDefinition as $key => $value) {
            if ($this->isReservedKey($key)) {
                continue;
            }

            $key = $this->stringFormatConverter->underscoreAndHyphenToCamelCase($key);
            $this->checkerConfigurationGuardian->ensurePropertyExists($checker, $key);

            $services[$checker]['properties'][$key] = $this->escapeValue($value);
        }

        return $services;
    }

    /**
     * @param mixed[] $services
     * @param mixed[] $serviceDefinition
     * @param string $serviceName
     * @return mixed[]
     */
    private function cleanupParameters(array $services, array $serviceDefinition, string $serviceName): array
    {
        foreach (array_keys($serviceDefinition) as $key) {
            if ($this->isReservedKey($key)) {
                continue;
            }

            unset($services[$serviceName][$key]);
        }

        return $services;
    }

    /**
     * @param string|int|bool $key
     * @return bool
     */
    private function isReservedKey($key): bool
    {
        if (!is_string($key)) {
            return false;
        }

        return in_array($key, self::SERVICE_KEYWORDS, true);
    }

    /**
     * @param string $checker
     * @param mixed[] $serviceDefinition
     * @return mixed[]
     */
    private function correctHeader(string $checker, array $serviceDefinition): array
    {
        // fixes comment extra bottom space
        if ($checker !== HeaderCommentFixer::class) {
            return $serviceDefinition;
        }

        if (isset($serviceDefinition['header'])) {
            $serviceDefinition['header'] = trim($serviceDefinition['header']);
        }

        return $serviceDefinition;
    }

    /**
     * @param mixed $value
     */
    private function escapeValue($value)
    {
        if (!is_array($value) && !is_string($value)) {
            return $value;
        }

        if (is_array($value)) {
            foreach ($value as $key => $nestedValue) {
                $value[$key] = $this->escapeValue($nestedValue);
            }

            return $value;
        }

        return Strings::replace($value, '#^@#', '@@');
    }
}
