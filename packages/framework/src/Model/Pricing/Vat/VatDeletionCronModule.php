<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Monolog\Logger;
use Override;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;

class VatDeletionCronModule implements IteratedCronModuleInterface
{
    protected Logger $logger;

    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly ProductInputPriceFacade $productInputPriceFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    #[Override]
    public function sleep(): void
    {
        $this->vatFacade->replaceVatForVatsMarkedForDeletion();
        $deletedVatsCount = $this->vatFacade->deleteAllReplacedVats();
        $this->logger->info('Deleted ' . $deletedVatsCount . ' vats');
    }

    #[Override]
    public function wakeUp(): void
    {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function iterate(): bool
    {
        $batchResult = $this->productInputPriceFacade->replaceBatchVatAndRecalculateInputPrices();

        if ($batchResult) {
            $this->logger->debug('Batch is done');
        } else {
            $this->vatFacade->replaceVatForVatsMarkedForDeletion();
            $deletedVatsCount = $this->vatFacade->deleteAllReplacedVats();
            $this->logger->debug('All vats are replaced');
            $this->logger->info('Deleted ' . $deletedVatsCount . ' vats');
        }

        return $batchResult;
    }
}
