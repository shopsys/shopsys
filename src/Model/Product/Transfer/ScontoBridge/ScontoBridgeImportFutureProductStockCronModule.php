<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportFutureProductStockCronModule implements SimpleCronModuleInterface
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

    public function run(): bool
    {
        return $this->scontoBridgeImportFutureProductStockFacade->runTransfer();
    }
}
