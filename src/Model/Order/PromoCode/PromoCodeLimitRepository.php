<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PromoCodeLimitRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return \App\Model\Order\PromoCode\PromoCodeLimit[]
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
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param \App\Model\Order\PromoCode\PromoCode $promoCode
     * @param string $price
     * @return \App\Model\Order\PromoCode\PromoCodeLimit|null
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
