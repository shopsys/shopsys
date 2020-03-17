<?php

declare(strict_types=1);

namespace App\Component\ScontoBridge\Transfer;

use App\Component\ScontoBridge\ScontoBridgeConfig;
use App\Model\Transfer\TransferLoggerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ScontoBridgeImportTransferDependency
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade
     */
    protected $sqlLoggerFacade;

    /**
     * @var \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    protected $validator;

    /**
     * @var \App\Component\ScontoBridge\ScontoBridgeConfig
     */
    private $scontoBridgeConfig;

    /**
     * @var \App\Model\Transfer\TransferLoggerFactory
     */
    private $transferLoggerFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\ScontoBridge\ScontoBridgeConfig $scontoBridgeConfig
     */
    public function __construct(
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        TransferLoggerFactory $transferLoggerFactory,
        ValidatorInterface $validator,
        ScontoBridgeConfig $scontoBridgeConfig
    ) {
        $this->em = $em;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->validator = $validator;
        $this->scontoBridgeConfig = $scontoBridgeConfig;
        $this->transferLoggerFactory = $transferLoggerFactory;
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
     * @return \Symfony\Component\Validator\Validator\ValidatorInterface
     */
    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    /**
     * @return \App\Component\ScontoBridge\ScontoBridgeConfig
     */
    public function getScontoBridgeConfig(): ScontoBridgeConfig
    {
        return $this->scontoBridgeConfig;
    }

    /**
     * @return \App\Model\Transfer\TransferLoggerFactory
     */
    public function getTransferLoggerFactory(): TransferLoggerFactory
    {
        return $this->transferLoggerFactory;
    }
}
