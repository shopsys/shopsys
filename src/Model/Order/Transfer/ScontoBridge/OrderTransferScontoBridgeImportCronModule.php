<?php

declare(strict_types=1);

namespace App\Model\Order\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class OrderTransferScontoBridgeImportCronModule implements SimpleCronModuleInterface
{
    /**
     * @var OrderTransferScontoBridgeImportFacade
     */
    private OrderTransferScontoBridgeImportFacade $importFacade;

    /**
     * @param OrderTransferScontoBridgeImportFacade $importFacade
     */
    public function __construct(OrderTransferScontoBridgeImportFacade $importFacade)
    {
        $this->importFacade = $importFacade;
    }

    public function setLogger(Logger $logger)
    {
    }

    public function run(): void
    {
        $this->importFacade->runTransfer();
    }
}
