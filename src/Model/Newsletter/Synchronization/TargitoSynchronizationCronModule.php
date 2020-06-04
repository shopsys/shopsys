<?php

declare(strict_types=1);

namespace App\Model\Newsletter\Synchronization;

use App\Component\Targito\Exception\TargitoNotEnabledException;
use App\Component\Targito\TargitoNewsletterFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class TargitoSynchronizationCronModule implements SimpleCronModuleInterface
{
    /**
     * @var \App\Component\Targito\TargitoNewsletterFacade
     */
    private $targitoNewsletterFacade;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    private $logger;

    /**
     * @param \App\Component\Targito\TargitoNewsletterFacade $targitoNewsletterFacade
     */
    public function __construct(TargitoNewsletterFacade $targitoNewsletterFacade)
    {
        $this->targitoNewsletterFacade = $targitoNewsletterFacade;
    }

    /**
     * @inheritDoc
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function run()
    {
        try {
            $this->targitoNewsletterFacade->runSynchronization();
        } catch (TargitoNotEnabledException $exception) {
            $this->logger->addError('Targito synchronization error: ' . $exception->getMessage());
        }
    }
}
