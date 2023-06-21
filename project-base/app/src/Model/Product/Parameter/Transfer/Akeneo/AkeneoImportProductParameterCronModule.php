<?php

declare(strict_types=1);

namespace App\Model\Product\Parameter\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductParameterCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade
     * @param \App\Model\Product\Parameter\Transfer\Akeneo\AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade
     */
    public function __construct(
        private AkeneoImportProductParameterFacade $akeneoImportProductParameterFacade,
        private AkeneoImportProductGroupParameterFacade $akeneoImportProductGroupParameterFacade,
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
        $this->akeneoImportProductGroupParameterFacade->runTransfer();
        $this->akeneoImportProductParameterFacade->runTransfer();
    }
}
