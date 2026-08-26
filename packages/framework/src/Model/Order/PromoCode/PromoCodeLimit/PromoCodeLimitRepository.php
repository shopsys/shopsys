<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeLimitRepository
{
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit[]
     */
    public function getLimitsByPromoCodeId(int $id): array
    {
        return $this->getQueryBuilder()
            ->select('l')
            ->from(PromoCodeLimit::class, 'l')
            ->where('l.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $id)
            ->getQuery()
            ->getResult();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    public function getHighestLimitByPromoCodeAndTotalPrice(
        PromoCode $promoCode,
        Money $totalPriceAmount,
    ): ?PromoCodeLimit {
        return $this->getQueryBuilder()
            ->select('l')
            ->from(PromoCodeLimit::class, 'l')
            ->where('l.fromPrice <= :totalPrice')
            ->setParameter('totalPrice', $totalPriceAmount->getAmount())
            ->andWhere('l.promoCode = :promoCode')
            ->setParameter('promoCode', $promoCode)
            ->orderBy('l.fromPrice', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
