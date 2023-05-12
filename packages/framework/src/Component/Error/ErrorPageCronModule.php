<?php

namespace Shopsys\FrameworkBundle\Component\Error;

use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Symfony\Bridge\Monolog\Logger;

class ErrorPageCronModule implements SimpleCronModuleInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Error\ErrorPagesFacade $errorPagesFacade
     */
    public function __construct(protected readonly ErrorPagesFacade $errorPagesFacade)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(Logger $logger)
    {
    }

    public function run()
    {
        $this->errorPagesFacade->generateAllErrorPagesForProduction();
    }
}
