<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class HeurekaCategoryCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
        protected readonly HeurekaCategoryFacade $heurekaCategoryFacade,
        protected readonly Domain $domain,
    ) {
    }

    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function run(): void
    {
        try {
            foreach ($this->heurekaCategoryDownloader->getSupportedLocales() as $locale) {
                if (!$this->domain->anyDomainHasLocale($locale)) {
                    continue;
                }

                $heurekaCategoriesData = $this->heurekaCategoryDownloader->getHeurekaCategories($locale);
                $this->heurekaCategoryFacade->saveHeurekaCategories($heurekaCategoriesData, $locale);
            }
        } catch (HeurekaCategoryDownloadFailedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
