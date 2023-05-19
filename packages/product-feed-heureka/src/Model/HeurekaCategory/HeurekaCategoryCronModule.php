<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class HeurekaCategoryCronModule implements SimpleCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryDownloader $heurekaCategoryDownloader
     * @param \Shopsys\ProductFeed\HeurekaBundle\Model\HeurekaCategory\HeurekaCategoryFacade $heurekaCategoryFacade
     */
    public function __construct(
        protected readonly HeurekaCategoryDownloader $heurekaCategoryDownloader,
        protected readonly HeurekaCategoryFacade $heurekaCategoryFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
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
