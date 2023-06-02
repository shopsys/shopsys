<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\Akeneo;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class AkeneoImportProductCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\Akeneo\AkeneoImportProductFacade
     */
    protected $akeneoImportProductFacade;

    /**
     * @param \App\Model\Product\Transfer\Akeneo\AkeneoImportProductFacade $akeneoImportProductFacade
     */
    public function __construct(AkeneoImportProductFacade $akeneoImportProductFacade)
    {
        $this->akeneoImportProductFacade = $akeneoImportProductFacade;
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
