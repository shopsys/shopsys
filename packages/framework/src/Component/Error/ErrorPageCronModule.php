<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Error;

use Psr\Log\LoggerInterface;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;

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
    public function setLogger(LoggerInterface $logger)
    {
    }

    public function run()
    {
        $this->errorPagesFacade->generateAllErrorPagesForProduction();
    }
}
