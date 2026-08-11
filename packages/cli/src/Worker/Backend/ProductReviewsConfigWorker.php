<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Backend;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Config\Section\ProductReviewsSection;
use Shopsys\Cli\Model\YamlHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class ProductReviewsConfigWorker extends AbstractWorker
{
    private const string FILE_PATH = 'app/config/packages/shopsys_framework.yaml';

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
        return 'Updates product reviews enabled_domain_ids in shopsys_framework.yaml';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 760;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $filePath = $projectPath . '/' . self::FILE_PATH;

        $data = $this->yamlHandler->readYaml($filePath);

        $data['shopsys_framework']['product']['reviews']['enabled_domain_ids'] = $this->getEnabledDomainIds($config);

        $this->yamlHandler->writeYaml($filePath, $data, 6);

        return WorkerResult::success(
            'Updated shopsys_framework.yaml',
            filesModified: [self::FILE_PATH],
        );
    }

    /**
     * @return array<int>
     */
    private function getEnabledDomainIds(CoreProjectConfig $config): array
    {
        $enabledDomainIds = [];

        foreach ($config->domains as $domain) {
            if ($domain->getConfigSection(ProductReviewsSection::class)->enabled) {
                $enabledDomainIds[] = $domain->id;
            }
        }

        return $enabledDomainIds;
    }
}
