<?php

declare(strict_types=1);

namespace App\Model\Product\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportExposedProductCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportExposedProductFacade
     */
    private ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade;

    /**
     * @param \App\Model\Product\Transfer\ScontoBridge\ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade
     */
    public function __construct(ScontoBridgeImportExposedProductFacade $scontoBridgeImportExposedProductFacade)
    {
        $this->scontoBridgeImportExposedProductFacade = $scontoBridgeImportExposedProductFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run(): bool
    {
        return $this->scontoBridgeImportExposedProductFacade->runTransfer();
    }
}
