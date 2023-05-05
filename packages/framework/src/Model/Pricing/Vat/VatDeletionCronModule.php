<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade;
use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class VatDeletionCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade
     */
    protected $productInputPriceFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductInputPriceFacade $productInputPriceFacade
     */
    public function __construct(VatFacade $vatFacade, ProductInputPriceFacade $productInputPriceFacade)
    {
        $this->vatFacade = $vatFacade;
        $this->productInputPriceFacade = $productInputPriceFacade;
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
