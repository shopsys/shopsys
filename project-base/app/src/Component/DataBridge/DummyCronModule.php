<?php

declare(strict_types=1);

namespace App\Component\DataBridge;

use App\Component\DataBridge\Transfer\DummyImportTransferFacade;
use Monolog\Logger;
use Override;
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
     * @param \Monolog\Logger $logger
     */
    #[Override]
    public function setLogger(Logger $logger): void
    {
    }

    #[Override]
    public function run(): void
    {
        $this->dummyImportTransfer->runTransfer();
    }
}
