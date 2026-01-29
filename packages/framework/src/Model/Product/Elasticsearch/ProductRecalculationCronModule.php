<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Elasticsearch;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

class ProductRecalculationCronModule implements SimpleCronModuleInterface
{
    protected LoggerInterface $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    public function run(): void
    {
        $this->productRecalculationDispatcher->dispatchAllProducts();
        $this->logger->info('All products were dispatched for recalculation');
    }
}
