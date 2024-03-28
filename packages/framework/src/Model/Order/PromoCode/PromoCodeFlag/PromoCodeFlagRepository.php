<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PromoCodeFlagRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $id
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlag[]
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
