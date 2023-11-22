<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyDisabledException;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\SqlLoggerAlreadyEnabledException;

class SqlLoggerFacade
{
    protected ?SQLLogger $sqlLogger = null;

    protected bool $isLoggerTemporarilyDisabled;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
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
        $this->sqlLogger = $this->em->getConnection()->getConfiguration()->getSQLLogger();
        $this->em->getConnection()->getConfiguration()->setSQLLogger(null);
        $this->isLoggerTemporarilyDisabled = true;
    }

    public function reenableLogging(): void
    {
        if (!$this->isLoggerTemporarilyDisabled) {
            $message = 'Trying to reenable already enabled SQL logger.';

            throw new SqlLoggerAlreadyEnabledException($message);
        }
        $this->em->getConnection()->getConfiguration()->setSQLLogger($this->sqlLogger);
        $this->sqlLogger = null;
        $this->isLoggerTemporarilyDisabled = false;
    }
}
