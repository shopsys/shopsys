<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Elasticsearch;

use Symfony\Component\Yaml\Yaml;

class IndexDefinitionYamlResolver
{
    protected const string SUFFIX_SEPARATOR = '@';
    protected const string DOMAIN_PREFIX = 'domain-';
    protected const string LOCALE_PREFIX = 'locale-';

    /**
     * @param string $yamlContent
     * @param int $domainId
     * @param string $locale
     * @param string $environment
     * @return array<string, mixed>
     */
    public function resolveYamlToDefinition(
        string $yamlContent,
        int $domainId,
        string $locale,
        string $environment,
    ): array {
        $yamlData = Yaml::parse($yamlContent);

        $index = $this->resolveSection($yamlData, 'index', $domainId, $locale, $environment);
        $analysis = $this->resolveSection($yamlData, 'analysis', $domainId, $locale, $environment);
        $mappings = $this->resolveSection($yamlData, 'mappings', $domainId, $locale, $environment);

        $resolvedMappings = $mappings !== null
            ? $this->resolveMappingFields($mappings, $domainId, $locale, $environment)
            : [];

        $result = [
            'settings' => [],
            'mappings' => ['properties' => $resolvedMappings],
        ];

        if ($index !== null) {
            $result['settings']['index'] = $index;
        }

        if ($analysis !== null) {
            $result['settings']['analysis'] = $analysis;
        }

        return $this->replacePlaceholders($result, $locale, $domainId);
    }

    /**
     * Resolves a root-level section (index, analysis, mappings) by finding the best matching
     * variant based on @domain-N, @locale-xx, @env suffixes.
     *
     * Priority: domain > locale > environment > base (no suffix)
     *
     * @param array<string, mixed> $yamlData
     * @param string $sectionName
     * @param int $domainId
     * @param string $locale
     * @param string $environment
     * @return array<string, mixed>|null
     */
    protected function resolveSection(
        array $yamlData,
        string $sectionName,
        int $domainId,
        string $locale,
        string $environment,
    ): ?array {
        $domainKey = $sectionName . self::SUFFIX_SEPARATOR . self::DOMAIN_PREFIX . $domainId;
        $localeKey = $sectionName . self::SUFFIX_SEPARATOR . self::LOCALE_PREFIX . $locale;
        $envKey = $sectionName . self::SUFFIX_SEPARATOR . $environment;

        if (isset($yamlData[$domainKey])) {
            return $yamlData[$domainKey];
        }

        if (isset($yamlData[$localeKey])) {
            return $yamlData[$localeKey];
        }

        if (isset($yamlData[$envKey])) {
            return $yamlData[$envKey];
        }

        return $yamlData[$sectionName] ?? null;
    }

    /**
     * Resolves individual mapping fields with @-suffix overrides.
     * Fields that only exist with a non-matching suffix are excluded.
     *
     * @param array<string, mixed> $mappings
     * @param int $domainId
     * @param string $locale
     * @param string $environment
     * @return array<string, mixed>
     */
    protected function resolveMappingFields(
        array $mappings,
        int $domainId,
        string $locale,
        string $environment,
    ): array {
        $grouped = $this->groupFieldsBySuffix($mappings);
        $resolved = [];

        foreach ($grouped as $baseFieldName => $variants) {
            $resolvedValue = $this->resolveFieldVariants($variants, $domainId, $locale, $environment);

            if ($resolvedValue !== null) {
                $resolved[$baseFieldName] = $resolvedValue;
            }
        }

        return $resolved;
    }

    /**
     * Groups mapping fields by their base name (without @ suffix).
     *
     * @param array<string, mixed> $mappings
     * @return array<string, array<string, mixed>> map of baseName => [suffix => value, ...]
     */
    protected function groupFieldsBySuffix(array $mappings): array
    {
        $grouped = [];

        foreach ($mappings as $key => $value) {
            $separatorPos = strpos($key, self::SUFFIX_SEPARATOR);

            if ($separatorPos !== false) {
                $baseName = substr($key, 0, $separatorPos);
                $suffix = substr($key, $separatorPos + 1);
                $grouped[$baseName][$suffix] = $value;
            } else {
                $grouped[$key][self::SUFFIX_SEPARATOR] = $value;
            }
        }

        return $grouped;
    }

    /**
     * From a set of field variants (base + suffixed), picks the best matching one.
     *
     * @param array<string, mixed> $variants map of suffix => value (base uses '@' as key)
     * @param int $domainId
     * @param string $locale
     * @param string $environment
     * @return array<string, mixed>|null
     */
    protected function resolveFieldVariants(
        array $variants,
        int $domainId,
        string $locale,
        string $environment,
    ): ?array {
        $domainSuffix = self::DOMAIN_PREFIX . $domainId;
        $localeSuffix = self::LOCALE_PREFIX . $locale;

        if (isset($variants[$domainSuffix])) {
            return $variants[$domainSuffix];
        }

        if (isset($variants[$localeSuffix])) {
            return $variants[$localeSuffix];
        }

        if (isset($variants[$environment])) {
            return $variants[$environment];
        }

        return $variants[self::SUFFIX_SEPARATOR] ?? null;
    }

    /**
     * Recursively replaces %domain_locale% and %domain_id% placeholders in the definition.
     *
     * @param array<string, mixed> $data
     * @param string $locale
     * @param int $domainId
     * @return array<string, mixed>
     */
    protected function replacePlaceholders(array $data, string $locale, int $domainId): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->replacePlaceholders($value, $locale, $domainId);
            } elseif (is_string($value)) {
                $result[$key] = str_replace(
                    ['%domain_locale%', '%domain_id%'],
                    [$locale, (string)$domainId],
                    $value,
                );
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
