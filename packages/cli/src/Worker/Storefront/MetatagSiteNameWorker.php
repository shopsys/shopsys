<?php

declare(strict_types=1);

namespace Shopsys\Cli\Worker\Storefront;

use Override;
use Shopsys\Cli\Config\CoreProjectConfig;
use Shopsys\Cli\Config\Section\MetatagSiteNameSection;
use Shopsys\Cli\Model\JsonHandler;
use Shopsys\Cli\Worker\AbstractWorker;
use Shopsys\Cli\Worker\WorkerResult;

final class MetatagSiteNameWorker extends AbstractWorker
{
    private const string LOCALES_PATH = 'storefront/public/locales';

    /**
     * @param \Shopsys\Cli\Model\JsonHandler $jsonHandler
     */
    public function __construct(
        private readonly JsonHandler $jsonHandler,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getDescription(): string
    {
        return 'Updates metatagSiteName in storefront locale common.json files';
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPriority(): int
    {
        return 368;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function execute(CoreProjectConfig $config, string $projectPath): WorkerResult
    {
        $metatagSection = $config->getConfigSection(MetatagSiteNameSection::class);
        $siteName = $metatagSection->siteName;

        $localesPath = $projectPath . '/' . self::LOCALES_PATH;
        $locales = $config->getUniqueLocales();

        $filesModified = [];

        foreach ($locales as $locale) {
            $commonJsonPath = $localesPath . '/' . $locale . '/common.json';

            if (!file_exists($commonJsonPath)) {
                continue;
            }

            $data = $this->jsonHandler->readJson($commonJsonPath);
            $data['metatagSiteName'] = $siteName;
            $this->jsonHandler->writeJson($commonJsonPath, $data);

            $filesModified[] = self::LOCALES_PATH . '/' . $locale . '/common.json';
        }

        if ($filesModified === []) {
            return WorkerResult::success('No locale common.json files found to update');
        }

        return WorkerResult::success(
            sprintf('Updated metatagSiteName to "%s" in %d locale file(s)', $siteName, count($filesModified)),
            filesModified: $filesModified,
        );
    }
}
