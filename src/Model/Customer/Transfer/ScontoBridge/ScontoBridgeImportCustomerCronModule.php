<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportCustomerCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Model\Customer\Transfer\ScontoBridge\ScontoBridgeImportCustomerFacade
     */
    private $scontoBridgeImportCustomerFacade;

    /**
     * @param \App\Model\Customer\Transfer\ScontoBridge\ScontoBridgeImportCustomerFacade $scontoBridgeImportCustomerFacade
     */
    public function __construct(ScontoBridgeImportCustomerFacade $scontoBridgeImportCustomerFacade)
    {
        $this->scontoBridgeImportCustomerFacade = $scontoBridgeImportCustomerFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run(): bool
    {
        return $this->scontoBridgeImportCustomerFacade->runTransfer();
    }
}
