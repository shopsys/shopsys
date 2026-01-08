<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Model\YamlHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class DomainConfigWorker extends AbstractWorker
{
    private const string DOMAINS_URLS_FILE_PATH = 'app/config/domains_urls.yaml.dist';
    private const string DOMAINS_FILE_PATH = 'app/config/domains.yaml';

    /**
     * @param \Shopsys\Cli\Model\YamlHandler $yamlHandler
     */
    public function __construct(
        private readonly YamlHandler $yamlHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates app/config/domains_urls.yaml.dist and app/config/domains.yaml with requested domain setup';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 1024;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $this->updateDomainsUrlsYamlFile($config, $projectPath);

        $this->updateDomainsYamlFile($config, $projectPath);

        return WorkerResult::success(
            'Domain files updated',
            filesModified: [self::DOMAINS_URLS_FILE_PATH, self::DOMAINS_FILE_PATH],
            hints: [
                'Domain URLs are set to default values (127.0.0.1:8000, 127.0.0.2:8000, ...).',
                'To customize URLs or use path fragments, edit domains on backend and storefront after setup.',
            ],
        );
    }

    /**
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @param string $projectPath
     */
    private function updateDomainsUrlsYamlFile(CoreProjectConfig $config, string $projectPath): void
    {
        $filePath = $projectPath . '/' . self::DOMAINS_URLS_FILE_PATH;

        $domainsUrls = [];

        foreach ($config->domains as $domain) {
            $domainsUrls[] = [
                'id' => $domain->id,
                'url' => sprintf('http://127.0.0.%d:8000', $domain->id),
            ];
        }

        $this->yamlHandler->writeYaml(
            $filePath,
            ['domains_urls' => $domainsUrls],
        );
    }

    /**
     * @param \Shopsys\Cli\Config\CoreProjectConfig $config
     * @param string $projectPath
     */
    private function updateDomainsYamlFile(CoreProjectConfig $config, string $projectPath): void
    {
        $filePath = $projectPath . '/' . self::DOMAINS_FILE_PATH;
        $domains = [];

        foreach ($config->domains as $domain) {
            $domains[] = [
                'id' => $domain->id,
                'load_demo_data' => $domain->loadDemoData,
                'locale' => $domain->locale,
                'name' => $domain->name,
                'timezone' => $domain->timezone,
                'type' => $domain->type,
            ];
        }

        $this->yamlHandler->writeYaml($filePath, ['domains' => $domains]);
    }
}
