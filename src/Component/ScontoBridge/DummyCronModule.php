<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge;

use App\Component\ScontoBridge\Transfer\DummyImportTransferFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DummyCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\ScontoBridge\Transfer\DummyImportTransferFacade
     */
    private DummyImportTransferFacade $dummyImportTransfer;

    /**
     * @param \App\Component\ScontoBridge\Transfer\DummyImportTransferFacade $dummyImportTransfer
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
        return;
    }

    public function run(): void
    {
        $this->dummyImportTransfer->runTransfer();
    }
}
