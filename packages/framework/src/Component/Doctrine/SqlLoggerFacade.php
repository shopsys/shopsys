<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Driver\Middleware as MiddlewareInterface;
use Doctrine\DBAL\Logging\Middleware as LoggingMiddleware;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyDisabledException;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyEnabledException;

class SqlLoggerFacade
{
    /**
     * @var array<\Doctrine\DBAL\Driver\Middleware>|null
     */
    protected ?array $originalMiddlewares = null;

    protected bool $isLoggerTemporarilyDisabled;

    public function __construct(protected readonly EntityManagerInterface $em)
    {
        $this->isLoggerTemporarilyDisabled = false;
    }

    public function temporarilyDisableLogging(): void
    {
        if ($this->isLoggerTemporarilyDisabled) {
            $message = 'Trying to disable already disabled SQL logger.';

            throw new SqlLoggerAlreadyDisabledException($message);
        }

        $configuration = $this->em->getConnection()->getConfiguration();
        $this->originalMiddlewares = $configuration->getMiddlewares();

        $middlewaresWithoutLogging = array_values(array_filter(
            $this->originalMiddlewares,
            fn (MiddlewareInterface $middleware) => !($middleware instanceof LoggingMiddleware),
        ));

        $configuration->setMiddlewares($middlewaresWithoutLogging);
        $this->isLoggerTemporarilyDisabled = true;
    }

    public function reenableLogging(): void
    {
        if (!$this->isLoggerTemporarilyDisabled) {
            $message = 'Trying to reenable already enabled SQL logger.';

            throw new SqlLoggerAlreadyEnabledException($message);
        }

        $this->em->getConnection()->getConfiguration()->setMiddlewares($this->originalMiddlewares);
        $this->originalMiddlewares = null;
        $this->isLoggerTemporarilyDisabled = false;
    }
}
