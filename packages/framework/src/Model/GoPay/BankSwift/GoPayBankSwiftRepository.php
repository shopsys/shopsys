<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;

class GoPayBankSwiftRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getBankSwiftRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayBankSwift::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $paymentMethod
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getAllIndexedBySwiftByPaymentMethod(GoPayPaymentMethod $paymentMethod): array
    {
        return $this->getBankSwiftRepository()
            ->createQueryBuilder('bs')
            ->indexBy('bs', 'bs.swift')
            ->where('bs.goPayPaymentMethod = :paymentMethod')
            ->setParameter('paymentMethod', $paymentMethod)
            ->getQuery()
            ->execute();
    }
}
