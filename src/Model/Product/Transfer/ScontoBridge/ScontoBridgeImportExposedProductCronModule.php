<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportExposedProductCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportExposedProductFacade
     */
    private ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade;

    /**
     * @param \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade
     */
    public function __construct(ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade)
    {
        $this->scontoBridgeImportExposedProductFacade = $scontoBridgeImportExposedProductFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * @inheritDoc
     */
    public function wakeUp()
    {
        $this->scontoBridgeImportExposedProductFacade->cronWakeUp();
    }

    /**
     * @inheritDoc
     */
    public function iterate()
    {
        $this->scontoBridgeImportExposedProductFacade->cronBatchSize = ScontoBridgeImportFutureProductStockFacade::PAGE_SIZE_LIMIT * 5;
        return $this->scontoBridgeImportExposedProductFacade->runTransfer();
    }

    /**
     * @inheritDoc
     */
    public function sleep()
    {
        $this->scontoBridgeImportExposedProductFacade->cronSleep();
    }
}
