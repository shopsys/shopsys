<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer;

use App\Component\Akeneo\AkeneoConfig;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AkeneoImportTransferDependency
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    protected $sqlLoggerFacade;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var \App\Component\Akeneo\AkeneoConfig
     */
    private $akeneoConfig;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Bridge\Monolog\Logger $logger
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     */
    public function __construct(
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        Logger $logger,
        ValidatorInterface $validator,
        AkeneoConfig $akeneoConfig
    ) {
        $this->em = $em;
        $this->logger = $logger;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->validator = $validator;
        $this->akeneoConfig = $akeneoConfig;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    public function getSqlLoggerFacade(): SqlLoggerFacade
    {
        return $this->sqlLoggerFacade;
    }

    /**
     * @return \Doctrine\ORM\EntityManagerInterface
     */
    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    /**
     * @return \Symfony\Bridge\Monolog\Logger
     */
    public function getLogger(): Logger
    {
        return $this->logger;
    }

    /**
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @return \App\Component\Akeneo\AkeneoConfig
     */
    public function getAkeneoConfig(): AkeneoConfig
    {
        return $this->akeneoConfig;
    }
}
