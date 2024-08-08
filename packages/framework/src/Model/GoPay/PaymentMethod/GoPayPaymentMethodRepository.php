<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\GoPay\BankSwift\GoPayBankSwift;

class GoPayPaymentMethodRepository
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
    protected function getPaymentMethodRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayPaymentMethod::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAll(): array
    {
        return $this->getPaymentMethodRepository()->findAll();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAllIndexedByIdentifierByDomainId(int $domainId): array
    {
        return $this->getPaymentMethodRepository()
            ->createQueryBuilder('pm')
            ->where('pm.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->indexBy('pm', 'pm.identifier')
            ->getQuery()
            ->execute();
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
     * @param int $currencyId
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

        return $queryBuilder->getQuery()->execute();
    }
}
