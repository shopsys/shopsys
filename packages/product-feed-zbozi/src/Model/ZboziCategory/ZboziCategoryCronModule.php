<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\ZboziCategory;

use Override;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class ZboziCategoryCronModule implements SimpleCronModuleInterface
{
    protected LoggerInterface $logger;

    public function __construct(
        protected readonly ZboziCategoryDownloader $zboziCategoryDownloader,
        protected readonly ZboziCategoryFacade $zboziCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        try {
            foreach ($this->zboziCategoryDownloader->getSupportedLocales() as $locale) {
                if (!$this->domain->anyDomainHasLocale($locale)) {
                    continue;
                }

                $zboziCategoriesData = $this->zboziCategoryDownloader->getZboziCategories($locale);
                $this->zboziCategoryFacade->saveZboziCategories($zboziCategoriesData, $locale);
            }
        } catch (ZboziCategoryDownloadFailedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
