<?php

declare(strict_types=1);

namespace App\Model\GoPay\BankSwift;

use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class GoPayBankSwiftRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getBankSwiftRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayBankSwift::class);
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $paymentMethod
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift[]
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
