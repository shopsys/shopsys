<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportFlagCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportFlagFacade $akeneoImportFlagFacade
     */
    public function __construct(
        private readonly AkeneoImportFlagFacade $akeneoImportFlagFacade,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $this->akeneoImportFlagFacade->runTransfer();
    }
}
