<?php

declare(strict_types=1);

namespace App\Component\Akeneo\Transfer;

use App\Component\Akeneo\AkeneoConfig;
use App\Model\Transfer\TransferLoggerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AkeneoImportTransferDependency
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     */
    public function __construct(
        protected SqlLoggerFacade $sqlLoggerFacade,
        protected EntityManagerInterface $em,
        protected ValidatorInterface $validator,
        private AkeneoConfig $akeneoConfig,
        private TransferLoggerFactory $transferLoggerFactory,
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
     * @return \App\Component\Akeneo\AkeneoConfig
     */
    public function getAkeneoConfig(): AkeneoConfig
    {
        return $this->akeneoConfig;
    }

    /**
     * @return \App\Model\Transfer\TransferLoggerFactory
     */
    public function getTransferLoggerFactory(): TransferLoggerFactory
    {
        return $this->transferLoggerFactory;
    }
}
