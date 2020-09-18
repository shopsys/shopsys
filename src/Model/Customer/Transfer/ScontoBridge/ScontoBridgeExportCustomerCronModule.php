<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeExportCustomerCronModule implements SimpleCronModuleInterface
{
    /**
     * @var ScontoBridgeExportCustomerFacade
     */
    private ScontoBridgeExportCustomerFacade $exportCustomerFacade;

    public function __construct(ScontoBridgeExportCustomerFacade $exportCustomerFacade)
    {
        $this->exportCustomerFacade = $exportCustomerFacade;
    }

    public function run(): void
    {
        $this->exportCustomerFacade->exportCustomers();
    }

    public function setLogger(Logger $logger)
    {
    }
}
