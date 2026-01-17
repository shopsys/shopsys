<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Monolog\Logger;
use Override;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class HeurekaCategoryCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
        protected readonly HeurekaCategoryFacade $heurekaCategoryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function run()
    {
        try {
            $heurekaCategoriesData = $this->heurekaCategoryDownloader->getHeurekaCategories();
            $this->heurekaCategoryFacade->saveHeurekaCategories($heurekaCategoriesData);
        } catch (HeurekaCategoryDownloadFailedException $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
