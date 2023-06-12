<?php

declare(strict_types=1);

namespace App\Component\SsfwccBridge;

use App\Component\SsfwccBridge\Transfer\DummyImportTransferFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class DummyCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Component\SsfwccBridge\Transfer\DummyImportTransferFacade $dummyImportTransfer
     */
    public function __construct(private DummyImportTransferFacade $dummyImportTransfer)
    {
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
