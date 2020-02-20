<?php

declare(strict_types=1);

namespace App\Model\Customer\Transfer\ScontoBridge;

use Shopsys\Plugin\Cron\IteratedCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ScontoBridgeImportCustomerCronModule implements IteratedCronModuleInterface
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

    /**
     * @inheritDoc
     */
    public function wakeUp()
    {
        $this->scontoBridgeImportCustomerFacade->cronWakeUp();
    }

    /**
     * @inheritDoc
     */
    public function iterate()
    {
        $this->scontoBridgeImportCustomerFacade->cronBatchSize = ScontoBridgeImportCustomerFacade::PAGE_SIZE_LIMIT * 5;
        return $this->scontoBridgeImportCustomerFacade->runTransfer();
    }

    /**
     * @inheritDoc
     */
    public function sleep()
    {
        $this->scontoBridgeImportCustomerFacade->cronSleep();
    }
}
