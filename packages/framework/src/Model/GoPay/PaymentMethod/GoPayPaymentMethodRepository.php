<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class GoPayPaymentMethodRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getPaymentMethodRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayPaymentMethod::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAll(): array
    {
        return $this->getPaymentMethodRepository()->findBy([], ['available' => 'desc']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAllIndexedByIdentifierByDomainIdAndCurrency(int $domainId, Currency $currency): array
    {
        return $this->getPaymentMethodRepository()
            ->createQueryBuilder('pm')
            ->where('pm.domainId = :domainId')
            ->andWhere('pm.currency = :currency')
            ->setParameter('domainId', $domainId)
            ->setParameter('currency', $currency)
            ->indexBy('pm', 'pm.identifier')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return string[]
     */
    public function getAllTypeIdentifiers(): array
    {
        $allPaymentMethods = $this->getAll();
        $availableTypeIdentifiers = [];

        foreach ($allPaymentMethods as $paymentMethod) {
            $availableTypeIdentifiers[] = $paymentMethod->getIdentifier();
        }

        return $availableTypeIdentifiers;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getBankSwiftsByCurrencyId(int $currencyId): array
    {
        $queryBuilder = $this->getPaymentMethodRepository()
            ->createQueryBuilder('pm')
            ->select('gbs')
            ->join(GoPayBankSwift::class, 'gbs', Join::WITH, 'pm = gbs.goPayPaymentMethod')
            ->where('pm.currency = :currency')
            ->setParameter('currency', $currencyId);

        return $queryBuilder->getQuery()->getResult();
    }
}
