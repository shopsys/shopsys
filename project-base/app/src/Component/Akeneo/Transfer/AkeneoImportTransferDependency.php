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
     * @var \App\Component\Akeneo\AkeneoConfig
     */
    private $akeneoConfig;

    /**
     * @var \App\Model\Transfer\TransferLoggerFactory
     */
    private $transferLoggerFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\Akeneo\AkeneoConfig $akeneoConfig
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     */
    public function __construct(
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        AkeneoConfig $akeneoConfig,
        TransferLoggerFactory $transferLoggerFactory
    ) {
        $this->em = $em;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->validator = $validator;
        $this->akeneoConfig = $akeneoConfig;
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
