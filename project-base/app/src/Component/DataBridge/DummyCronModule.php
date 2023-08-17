<?php

declare(strict_types=1);

namespace App\Component\DataBridge;

use App\Component\DataBridge\Transfer\DummyImportTransferFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DummyCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Component\DataBridge\Transfer\DummyImportTransferFacade $dummyImportTransfer
     */
    public function __construct(
        private readonly DummyImportTransferFacade $dummyImportTransfer,
    ) {
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger): void
    {
    }

    public function run(): void
    {
        $this->dummyImportTransfer->runTransfer();
    }
}
