<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportProductStockCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportProductStockFacade
     */
    private $scontoBridgeImportProductStockFacade;

    /**
     * @param \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportProductStockFacade $scontoBridgeImportProductStockFacade
     */
    public function __construct(ScontoBridgeImportProductStockFacade $scontoBridgeImportProductStockFacade)
    {
        $this->scontoBridgeImportProductStockFacade = $scontoBridgeImportProductStockFacade;
    }

    /**
     * @inheritDoc
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run(): bool
    {
        return $this->scontoBridgeImportProductStockFacade->runTransfer();
    }
}
