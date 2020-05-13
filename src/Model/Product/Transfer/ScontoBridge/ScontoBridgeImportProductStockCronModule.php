<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportProductStockCronModule implements IteratedCronModuleInterface
{


    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportProductStockFacade
     */
    private $scontoBridgeImportProductStockFacade;

    public function __construct(ScontoBridgeImportProductStockFacade $scontoBridgeImportProductStockFacade)
    {

        $this->scontoBridgeImportProductStockFacade = $scontoBridgeImportProductStockFacade;
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
        $this->scontoBridgeImportProductStockFacade->cronWakeUp();
    }

    /**
     * @inheritDoc
     */
    public function iterate()
    {
        $this->scontoBridgeImportProductStockFacade->cronBatchSize = ScontoBridgeImportProductStockFacade::PAGE_SIZE_LIMIT * 5;
        return $this->scontoBridgeImportProductStockFacade->runTransfer();
    }

    /**
     * @inheritDoc
     */
    public function sleep()
    {
        $this->scontoBridgeImportProductStockFacade->cronSleep();
    }
}
