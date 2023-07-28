<?php

declare(strict_types=1);

namespace App\Component\DataBridge\Transfer;

use App\Component\DataBridge\BridgeConfig;
use App\Model\Transfer\TransferLoggerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BridgeImportTransferDependency
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\DataBridge\BridgeConfig $bridgeConfig
     */
    public function __construct(
        protected SqlLoggerFacade $sqlLoggerFacade,
        protected EntityManagerInterface $em,
        private TransferLoggerFactory $transferLoggerFactory,
        protected ValidatorInterface $validator,
        private BridgeConfig $bridgeConfig,
    ) {
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
     * @return \App\Component\DataBridge\BridgeConfig
     */
    public function getBridgeConfig(): BridgeConfig
    {
        return $this->bridgeConfig;
    }

    /**
     * @return \App\Model\Transfer\TransferLoggerFactory
     */
    public function getTransferLoggerFactory(): TransferLoggerFactory
    {
        return $this->transferLoggerFactory;
    }
}
