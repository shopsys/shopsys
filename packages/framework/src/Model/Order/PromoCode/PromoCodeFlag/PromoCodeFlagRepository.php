<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PromoCodeFlagRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
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
            ->getResult();
    }

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
