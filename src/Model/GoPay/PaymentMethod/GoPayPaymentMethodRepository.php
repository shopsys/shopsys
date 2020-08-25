<?php

declare(strict_types=1);

namespace App\Model\GoPay\PaymentMethod;

use App\Model\GoPay\BankSwift\GoPayBankSwift;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

class GoPayPaymentMethodRepository
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
    private function getPaymentMethodRepository(): EntityRepository
    {
        return $this->em->getRepository(GoPayPaymentMethod::class);
    }

    /**
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAll(): array
    {
        return $this->getPaymentMethodRepository()->findAll();
    }

    /**
     * @param int $currencyId
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod[]
     */
    public function getAllIndexedByIdentifierByCurrencyId(int $currencyId): array
    {
        return $this->getPaymentMethodRepository()
            ->createQueryBuilder('pm')
            ->where('pm.currency = :currency')->setParameter('currency', $currencyId)
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
     * @return \App\Model\GoPay\BankSwift\GoPayBankSwift[]
     */
    public function getBankSwiftsByCurrencyId(int $currencyId): array
    {
        $queryBuilder = $this->getPaymentMethodRepository()
            ->createQueryBuilder('pm')
            ->select('pbs')
            ->join(GoPayBankSwift::class, 'pbs', Join::WITH, 'pm = pbs.goPayPaymentMethod')
            ->where('pm.currency = :currency')
            ->setParameter('currency', $currencyId);

        return $queryBuilder->getQuery()->execute();
    }
}
