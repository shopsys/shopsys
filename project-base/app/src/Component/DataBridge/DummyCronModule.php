<?php

declare(strict_types=1);

namespace App\Component\DataBridge;

use App\Component\DataBridge\Transfer\DummyImportTransferFacade;
use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

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
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger): void
    {
    }

    public function run(): void
    {
        $this->dummyImportTransfer->runTransfer();
    }
}
