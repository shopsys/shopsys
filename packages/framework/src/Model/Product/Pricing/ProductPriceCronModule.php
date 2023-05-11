<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ProductPriceCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    protected ProductPriceRecalculator $productPriceRecalculator;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator $productPriceRecalculator
     */
    public function __construct(ProductPriceRecalculator $productPriceRecalculator)
    {
        $this->productPriceRecalculator = $productPriceRecalculator;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function sleep()
    {
    }

    public function wakeUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterate()
    {
        if ($this->productPriceRecalculator->runBatchOfScheduledDelayedRecalculations()) {
            $this->logger->debug('Batch is recalculated.');

            return true;
        }
        $this->logger->debug('All prices are recalculated.');

        return false;
    }
}
