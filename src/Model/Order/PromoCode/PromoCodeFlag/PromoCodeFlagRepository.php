<?php

declare(strict_types=1);

namespace App\Model\Order\PromoCode\PromoCodeFlag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PromoCodeFlagRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $id
     * @return \App\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[]
     */
    public function getFlagsByPromoCodeId(int $id): array
    {
        return $this->getQueryBuilder()
            ->select('pcf')
            ->from(PromoCodeFlag::class, 'pcf')
            ->where('pcf.promoCode = :promoCodeId')
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
            ->delete(PromoCodeFlag::class, 'pcf')
            ->where('pcf.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $id)
            ->getQuery()
            ->execute();
    }
}
