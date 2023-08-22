<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductFacade $akeneoImportProductFacade
     */
    public function __construct(
        private readonly AkeneoImportProductFacade $akeneoImportProductFacade,
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
        $this->akeneoImportProductFacade->runTransfer();
    }
}
