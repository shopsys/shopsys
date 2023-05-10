<?php

declare(strict_types=1);

namespace App\Component\SsfwccBridge\Transfer;

use App\Component\SsfwccBridge\BridgeConfig;
use App\Model\Transfer\TransferLoggerFactory;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class BridgeImportTransferDependency
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
     * @var \App\Component\SsfwccBridge\BridgeConfig
     */
    private $bridgeConfig;

    /**
     * @var \App\Model\Transfer\TransferLoggerFactory
     */
    private $transferLoggerFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\SqlLoggerFacade $sqlLoggerFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Transfer\TransferLoggerFactory $transferLoggerFactory
     * @param \Symfony\Component\Validator\Validator\ValidatorInterface $validator
     * @param \App\Component\SsfwccBridge\BridgeConfig $bridgeConfig
     */
    public function __construct(
        SqlLoggerFacade $sqlLoggerFacade,
        EntityManagerInterface $em,
        TransferLoggerFactory $transferLoggerFactory,
        ValidatorInterface $validator,
        BridgeConfig $bridgeConfig
    ) {
        $this->em = $em;
        $this->sqlLoggerFacade = $sqlLoggerFacade;
        $this->validator = $validator;
        $this->bridgeConfig = $bridgeConfig;
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
     * @return \App\Component\SsfwccBridge\BridgeConfig
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
