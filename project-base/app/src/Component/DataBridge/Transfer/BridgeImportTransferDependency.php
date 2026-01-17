<?php

declare(strict_types=1);

namespace App\Component\DataBridge\Transfer;

use App\Component\DataBridge\BridgeConfig;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Shopsys\FrameworkBundle\Model\Transfer\TransferLoggerFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BridgeImportTransferDependency
{
    public function __construct(
        protected SqlLoggerFacade $sqlLoggerFacade,
        protected EntityManagerInterface $em,
        private TransferLoggerFactory $transferLoggerFactory,
        protected ValidatorInterface $validator,
        private BridgeConfig $bridgeConfig,
    ) {
    }

    public function getSqlLoggerFacade(): SqlLoggerFacade
    {
        return $this->sqlLoggerFacade;
    }

    public function getEm(): EntityManagerInterface
    {
        return $this->em;
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getBridgeConfig(): BridgeConfig
    {
        return $this->bridgeConfig;
    }

    public function getTransferLoggerFactory(): TransferLoggerFactory
    {
        return $this->transferLoggerFactory;
    }
}
