<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

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

    public function deleteByPromoCodeId(int $id): void
    {
        $this->getQueryBuilder()
            ->delete(PromoCodeLimit::class, 'l')
            ->where('l.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $id)
            ->getQuery()
            ->execute();
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    public function getHighestLimitByPromoCodeAndTotalPrice(
        PromoCode $promoCode,
        Money $totalPriceAmount,
        Currency $currency,
    ): ?PromoCodeLimit {
        return $this->getQueryBuilder()
            ->select('l')
            ->from(PromoCodeLimit::class, 'l')
            ->where('l.fromPrice <= :totalPrice')
            ->setParameter('totalPrice', $totalPriceAmount->getAmount())
            ->andWhere('l.promoCode = :promoCode')
            ->setParameter('promoCode', $promoCode)
            ->andWhere('l.currency = :currency')
            ->setParameter('currency', $currency)
            ->orderBy('l.fromPrice', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
