<?php

declare(strict_types=1);

namespace App\Component\SsfwccBridge;

use App\Component\SsfwccBridge\Transfer\DummyImportTransferFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DummyCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\SsfwccBridge\Transfer\DummyImportTransferFacade
     */
    private DummyImportTransferFacade $dummyImportTransfer;

    /**
     * @param \App\Component\SsfwccBridge\Transfer\DummyImportTransferFacade $dummyImportTransfer
     */
    public function __construct(DummyImportTransferFacade $dummyImportTransfer)
    {
        $this->dummyImportTransfer = $dummyImportTransfer;
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
