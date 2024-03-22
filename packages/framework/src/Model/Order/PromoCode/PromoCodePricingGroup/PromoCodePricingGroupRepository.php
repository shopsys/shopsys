<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class PromoCodePricingGroupRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(private EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodePricingGroup\PromoCodePricingGroup[]
     */
    public function getAllByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('pcpg')
            ->from(PromoCodePricingGroup::class, 'pcpg')
            ->where('pcpg.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup[]
     */
    public function getPricingGroupsByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('pg')
            ->from(PromoCodePricingGroup::class, 'pcpg')
            ->join('pcpg.pricingGroup', 'pg')
            ->where('pcpg.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }
}
