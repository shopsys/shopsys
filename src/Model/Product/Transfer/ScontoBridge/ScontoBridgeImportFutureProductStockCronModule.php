<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportFutureProductStockCronModule implements IteratedCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportFutureProductStockFacade
     */
    private $scontoBridgeImportFutureProductStockFacade;

    /**
     * @param \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportFutureProductStockFacade $scontoBridgeImportFutureProductStockFacade
     */
    public function __construct(ScontoBridgeImportFutureProductStockFacade $scontoBridgeImportFutureProductStockFacade)
    {
        $this->scontoBridgeImportFutureProductStockFacade = $scontoBridgeImportFutureProductStockFacade;
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
        $this->scontoBridgeImportFutureProductStockFacade->cronWakeUp();
    }

    /**
     * @inheritDoc
     */
    public function iterate()
    {
        $this->scontoBridgeImportFutureProductStockFacade->cronBatchSize = ScontoBridgeImportFutureProductStockFacade::PAGE_SIZE_LIMIT * 5;
        return $this->scontoBridgeImportFutureProductStockFacade->runTransfer();
    }

    /**
     * @inheritDoc
     */
    public function sleep()
    {
        $this->scontoBridgeImportFutureProductStockFacade->cronSleep();
    }
}
