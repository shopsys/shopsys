<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use RuntimeException;
use Shopsys\Cli\Config\CoreDomainConfig;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\FileHandler;
use Shopsys\Cli\Model\JsonHandler;
use Shopsys\Cli\Model\YamlHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;
use Symfony\Component\Finder\Finder;

final class ElasticsearchDefinitionWorker extends AbstractWorker
{
    private const string DEFINITION_DIR = 'app/src/Resources/definition';
    private const string LOCALE_CONFIG_PATH = __DIR__ . '/resources/elastic-locales.yaml';
    private const string ELASTICSEARCH_DOCKERFILE = 'app/docker/elasticsearch/Dockerfile';
    private const string DEFAULT_LOCALE = 'en';

    /**
     * @var array<string, array{stemmer: string|null, stopwords: string|array<string>, plugins?: array<string>}>|null
     */
    private ?array $localeConfig = null;

    public function __construct(
        private readonly JsonHandler $jsonHandler,
        private readonly YamlHandler $yamlHandler,
        private readonly FileHandler $fileHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates Elasticsearch index definitions with locale-specific analysis settings';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 880;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $definitionPath = $projectPath . '/' . self::DEFINITION_DIR;

        if (!is_dir($definitionPath)) {
            return WorkerResult::failure(sprintf('Definition directory not found: %s', self::DEFINITION_DIR));
        }

        $indexFolders = $this->getIndexFolders($definitionPath);

        if ($indexFolders === []) {
            return WorkerResult::failure('No index folders found');
        }

        $filesCreated = [];
        $filesModified = [];
        $filesDeleted = [];

        foreach ($indexFolders as $indexFolder) {
            $result = $this->processIndexFolder($indexFolder, $config, $projectPath);
            array_push($filesCreated, ...$result['created']);
            array_push($filesModified, ...$result['modified']);
            array_push($filesDeleted, ...$result['deleted']);
        }

        $dockerfileResult = $this->updateDockerfilePlugins($config, $projectPath);

        if ($dockerfileResult !== null) {
            $filesModified[] = $dockerfileResult;
        }

        return WorkerResult::success(
            'Elasticsearch definitions updated',
            filesModified: $filesModified,
            filesCreated: $filesCreated,
            filesDeleted: $filesDeleted,
        );
    }

    /**
     * @return array<string>
     */
    private function getIndexFolders(string $definitionPath): array
    {
        $finder = Finder::create()
            ->directories()
            ->in($definitionPath)
            ->depth(0)
            ->sortByName();

        $folders = [];

        foreach ($finder as $directory) {
            $folders[] = $directory->getRealPath();
        }

        return $folders;
    }

    /**
     * @throws \JsonException
     * @return array{created: array<string>, modified: array<string>, deleted: array<string>}
     */
    private function processIndexFolder(
        string $indexFolder,
        CoreProjectConfig $config,
        string $projectPath,
    ): array {
        $result = ['created' => [], 'modified' => [], 'deleted' => []];
        $templateData = $this->getTemplateData($indexFolder);

        if ($templateData === null) {
            throw new RuntimeException('No template data found');
        }

        foreach ($config->domains as $domain) {
            $filePath = $indexFolder . '/' . $domain->id . '.json';
            $relativePath = preg_replace(sprintf('#^%s#', preg_quote($projectPath, '#')), '', $filePath);

            $definitionData = $this->generateDefinitionForLocale($templateData, $domain);

            $fileExisted = file_exists($filePath);
            $this->jsonHandler->writeJson($filePath, $definitionData);

            if ($fileExisted) {
                $result['modified'][] = $relativePath;
            } else {
                $result['created'][] = $relativePath;
            }
        }

        $deletedFiles = $this->deleteUnusedDefinitions($indexFolder, $config);
        $result['deleted'] = array_merge($result['deleted'], $deletedFiles);

        return $result;
    }

    /**
     * @return array<mixed>|null
     */
    private function getTemplateData(string $indexFolder): ?array
    {
        $firstFile = $indexFolder . '/1.json';

        if (file_exists($firstFile)) {
            return $this->jsonHandler->readJson($firstFile);
        }

        $finderIterator = Finder::create()
            ->files()
            ->in($indexFolder)
            ->name('*.json')
            ->sortByName()
            ->getIterator();

        $finderIterator->rewind();
        $finderIterator->current();

        if (!$finderIterator->valid()) {
            return null;
        }

        return $this->jsonHandler->readJson($finderIterator->current()->getRealPath());
    }

    /**
     * @param array<mixed> $templateData
     * @return array<mixed>
     */
    private function generateDefinitionForLocale(array $templateData, CoreDomainConfig $domain): array
    {
        $locale = $domain->locale;
        $localeSettings = $this->getLocaleSettings($locale);

        $data = $templateData;
        $data = $this->updateAnalysisFilters($data, $localeSettings);
        $data = $this->updateStemmingAnalyzer($data, $localeSettings);
        $data = $this->updateIcuCollationLanguage($data, $locale);

        return $data;
    }

    /**
     * @return array{stemmer: string|null, stopwords: string|array<string>, prefix: string, builtin_filters: array<string>}
     */
    private function getLocaleSettings(string $locale): array
    {
        $config = $this->getLocaleConfig();

        if (isset($config[$locale])) {
            return [
                'prefix' => $config[$locale]['prefix'] ?? $locale,
                'stemmer' => $config[$locale]['stemmer'] ?? null,
                'stopwords' => $config[$locale]['stopwords'] ?? '_' . self::DEFAULT_LOCALE . '_',
                'builtin_filters' => $config[$locale]['builtin_filters'] ?? [],
            ];
        }

        return [
            'prefix' => $locale,
            'stemmer' => self::DEFAULT_LOCALE,
            'stopwords' => '_' . self::DEFAULT_LOCALE . '_',
            'builtin_filters' => [],
        ];
    }

    /**
     * @return array<string, array{stemmer: string|null, stopwords: string|array<string>, plugins?: array<string>}>
     */
    private function getLocaleConfig(): array
    {
        if ($this->localeConfig === null) {
            $data = $this->yamlHandler->readYaml(self::LOCALE_CONFIG_PATH);
            $this->localeConfig = $data['locales'] ?? [];
        }

        return $this->localeConfig;
    }

    /**
     * @param array<mixed> $data
     * @param array{stemmer: string|null, stopwords: string|array<string>, prefix: string, builtin_filters: array<string>} $localeSettings
     * @return array<mixed>
     */
    private function updateAnalysisFilters(array $data, array $localeSettings): array
    {
        if (!isset($data['settings']['analysis']['filter'])) {
            return $data;
        }

        $filters = $data['settings']['analysis']['filter'];
        $prefix = $localeSettings['prefix'];
        $useBuiltinFilters = $localeSettings['builtin_filters'] !== [];
        $newFilters = [];

        foreach ($filters as $filterName => $filterConfig) {
            if ($this->isStopFilter($filterName)) {
                if (!$useBuiltinFilters) {
                    $newFilters[$prefix . '_stop'] = $this->createStopFilter($localeSettings['stopwords']);
                }
            } elseif ($this->isStemmerFilter($filterName)) {
                if (!$useBuiltinFilters && $localeSettings['stemmer'] !== null) {
                    $newFilters[$prefix . '_stemmer'] = [
                        'type' => 'stemmer',
                        'language' => $localeSettings['stemmer'],
                    ];
                }
            } else {
                $newFilters[$filterName] = $filterConfig;
            }
        }

        $data['settings']['analysis']['filter'] = $newFilters;

        return $data;
    }

    private function isStopFilter(string $filterName): bool
    {
        return str_ends_with($filterName, '_stop');
    }

    private function isStemmerFilter(string $filterName): bool
    {
        return str_ends_with($filterName, '_stemmer');
    }

    /**
     * @param string|array<string> $stopwords
     * @return array<string, mixed>
     */
    private function createStopFilter(string|array $stopwords): array
    {
        return [
            'type' => 'stop',
            'stopwords' => $stopwords,
        ];
    }

    /**
     * @param array<mixed> $data
     * @param array{stemmer: string|null, stopwords: string|array<string>, prefix: string, builtin_filters: array<string>} $localeSettings
     * @return array<mixed>
     */
    private function updateStemmingAnalyzer(array $data, array $localeSettings): array
    {
        if (!isset($data['settings']['analysis']['analyzer']['stemming'])) {
            return $data;
        }

        $stemmingAnalyzer = $data['settings']['analysis']['analyzer']['stemming'];

        if (!isset($stemmingAnalyzer['filter']) || !is_array($stemmingAnalyzer['filter'])) {
            return $data;
        }

        $prefix = $localeSettings['prefix'];
        $builtinFilters = $localeSettings['builtin_filters'];
        $useBuiltinFilters = $builtinFilters !== [];
        $newFilters = [];
        $builtinFiltersAdded = false;

        foreach ($stemmingAnalyzer['filter'] as $filter) {
            if ($this->isStopFilter($filter) || $this->isStemmerFilter($filter)) {
                if ($useBuiltinFilters) {
                    if (!$builtinFiltersAdded) {
                        array_push($newFilters, ...$builtinFilters);
                        $builtinFiltersAdded = true;
                    }
                } elseif ($this->isStopFilter($filter)) {
                    $newFilters[] = $prefix . '_stop';
                } elseif ($localeSettings['stemmer'] !== null) {
                    $newFilters[] = $prefix . '_stemmer';
                }
            } else {
                $newFilters[] = $filter;
            }
        }

        $data['settings']['analysis']['analyzer']['stemming']['filter'] = $newFilters;

        return $data;
    }

    /**
     * @param array<mixed> $data
     * @return array<mixed>
     */
    private function updateIcuCollationLanguage(array $data, string $locale): array
    {
        if (!isset($data['mappings']['properties'])) {
            return $data;
        }

        $data['mappings']['properties'] = $this->updatePropertiesLanguage(
            $data['mappings']['properties'],
            $locale,
        );

        return $data;
    }

    /**
     * @param array<mixed> $properties
     * @return array<mixed>
     */
    private function updatePropertiesLanguage(array $properties, string $locale): array
    {
        foreach ($properties as $propertyName => $propertyConfig) {
            if (!is_array($propertyConfig)) {
                continue;
            }

            if (isset($propertyConfig['type']) && $propertyConfig['type'] === 'icu_collation_keyword') {
                $properties[$propertyName]['language'] = $locale;
            }

            if (isset($propertyConfig['fields'])) {
                $properties[$propertyName]['fields'] = $this->updatePropertiesLanguage(
                    $propertyConfig['fields'],
                    $locale,
                );
            }

            if (isset($propertyConfig['properties'])) {
                $properties[$propertyName]['properties'] = $this->updatePropertiesLanguage(
                    $propertyConfig['properties'],
                    $locale,
                );
            }
        }

        return $properties;
    }

    /**
     * @return array<string>
     */
    private function deleteUnusedDefinitions(
        string $indexFolder,
        CoreProjectConfig $config,
    ): array {
        $indexName = basename($indexFolder);
        $domainIds = $config->getAllDomainIds();
        $deleted = [];

        $finder = Finder::create()
            ->files()
            ->in($indexFolder)
            ->name('/^\d+\.json$/');

        foreach ($finder as $file) {
            $fileId = (int)pathinfo($file->getFilename(), PATHINFO_FILENAME);

            if (in_array($fileId, $domainIds, true)) {
                continue;
            }

            $this->fileHandler->deleteFile($file->getRealPath());
            $deleted[] = self::DEFINITION_DIR . '/' . $indexName . '/' . $file->getFilename();
        }

        return $deleted;
    }

    /**
     * @return string|null Relative path to modified Dockerfile, or null if no changes
     */
    private function updateDockerfilePlugins(CoreProjectConfig $config, string $projectPath): ?string
    {
        $dockerfilePath = $projectPath . '/' . self::ELASTICSEARCH_DOCKERFILE;

        $requiredPlugins = $this->getRequiredPlugins($config);

        if ($requiredPlugins === []) {
            return null;
        }

        $content = $this->fileHandler->readFile($dockerfilePath);
        $installedPlugins = $this->getInstalledPlugins($content);
        $missingPlugins = array_diff($requiredPlugins, $installedPlugins);

        if ($missingPlugins === []) {
            return null;
        }

        $newContent = $this->addPluginsToDockerfile($content, $missingPlugins);
        $this->fileHandler->writeFile($dockerfilePath, $newContent);

        return self::ELASTICSEARCH_DOCKERFILE;
    }

    /**
     * @return array<string>
     */
    private function getRequiredPlugins(CoreProjectConfig $config): array
    {
        $plugins = [];
        $localeConfig = $this->getLocaleConfig();

        foreach ($config->getUniqueLocales() as $locale) {
            if (isset($localeConfig[$locale]['plugins'])) {
                array_push($plugins, ...$localeConfig[$locale]['plugins']);
            }
        }

        return array_unique($plugins);
    }

    /**
     * @return array<string>
     */
    private function getInstalledPlugins(string $dockerfileContent): array
    {
        preg_match_all(
            '/elasticsearch-plugin\s+install\s+(\S+)/',
            $dockerfileContent,
            $matches,
        );

        return $matches[1];
    }

    /**
     * @param array<string> $plugins
     */
    private function addPluginsToDockerfile(string $content, array $plugins): string
    {
        $pluginLines = [];

        foreach ($plugins as $plugin) {
            $pluginLines[] = sprintf('RUN bin/elasticsearch-plugin install %s', $plugin);
        }

        $pluginBlock = implode("\n", $pluginLines);

        return rtrim($content) . "\n\n" . $pluginBlock . "\n";
    }
}
