<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;

class PromoCodeLimitRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected EntityManagerInterface $em)
    {
    }

    /**
     * @param int $id
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
            ->execute();
    }

    /**
     * @param int $id
     */
    public function deleteByPromoCodeId(int $id): void
    {
        $this->getQueryBuilder()
            ->delete(PromoCodeLimit::class, 'l')
            ->where('l.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $id)
            ->getQuery()
            ->execute();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode $promoCode
     * @param string $price
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit|null
     */
    public function getHighestLimitByPromoCodeAndTotalPrice(PromoCode $promoCode, string $price): ?PromoCodeLimit
    {
        return $this->getQueryBuilder()
            ->select('l')
            ->from(PromoCodeLimit::class, 'l')
            ->where('l.fromPriceWithVat <= :totalPrice')
            ->setParameter('totalPrice', $price)
            ->andWhere('l.promoCode = :promoCode')
            ->setParameter('promoCode', $promoCode)
            ->orderBy('l.fromPriceWithVat', 'desc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
