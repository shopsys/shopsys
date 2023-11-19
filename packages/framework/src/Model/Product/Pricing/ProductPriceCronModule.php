<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ProductPriceCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculator $productPriceRecalculator
     */
    public function __construct(protected readonly ProductPriceRecalculator $productPriceRecalculator)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function sleep(): void
    {
    }

    public function wakeUp(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterate(): bool
    {
        if ($this->productPriceRecalculator->runBatchOfScheduledDelayedRecalculations()) {
            $this->logger->debug('Batch is recalculated.');

            return true;
        }
        $this->logger->debug('All prices are recalculated.');

        return false;
    }
}
