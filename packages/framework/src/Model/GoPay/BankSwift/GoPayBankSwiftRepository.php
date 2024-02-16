<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\BankSwift;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

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

    /**
     * @param string $goPayBankSwift
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift|null
     */
    public function findBySwiftAndPaymentMethodAndCurrency(
        string $goPayBankSwift,
        GoPayPaymentMethod $goPayPaymentMethod,
        Currency $currency,
    ): ?GoPayBankSwift {
        $queryBuilder = $this->em->getRepository(GoPayPaymentMethod::class)
            ->createQueryBuilder('pm')
            ->select('gbs')
            ->join(GoPayBankSwift::class, 'gbs', Join::WITH, 'pm = gbs.goPayPaymentMethod')
            ->where('gbs.swift = :swift')
            ->andWhere('pm = :paymentMethod')
            ->andWhere('pm.currency = :currency')
            ->setParameter('swift', $goPayBankSwift)
            ->setParameter('paymentMethod', $goPayPaymentMethod)
            ->setParameter('currency', $currency);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
