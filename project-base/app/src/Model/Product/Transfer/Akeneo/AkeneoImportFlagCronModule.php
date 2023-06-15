<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportFlagCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportFlagFacade
     */
    private $akeneoImportFlagFacade;

    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportFlagFacade $akeneoImportFlagFacade
     */
    public function __construct(AkeneoImportFlagFacade $akeneoImportFlagFacade)
    {
        $this->akeneoImportFlagFacade = $akeneoImportFlagFacade;
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
