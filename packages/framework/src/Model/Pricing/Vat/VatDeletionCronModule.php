<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class VatDeletionCronModule implements IteratedCronModuleInterface
{
    protected LoggerInterface $logger;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly ProductInputPriceFacade $productInputPriceFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function sleep()
    {
        $deletedVatsCount = $this->vatFacade->deleteAllReplacedVats();
        $this->logger->info('Deleted ' . $deletedVatsCount . ' vats');
    }

    public function wakeUp()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function iterate()
    {
        $batchResult = $this->productInputPriceFacade->replaceBatchVatAndRecalculateInputPrices();

        if ($batchResult) {
            $this->logger->debug('Batch is done');
        } else {
            $deletedVatsCount = $this->vatFacade->deleteAllReplacedVats();
            $this->logger->debug('All vats are replaced');
            $this->logger->info('Deleted ' . $deletedVatsCount . ' vats');
        }

        return $batchResult;
    }
}
