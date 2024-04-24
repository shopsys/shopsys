<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Product;

class PromoCodeBrandRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrand[]
     */
    public function getAllByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('pcb')
            ->from(PromoCodeBrand::class, 'pcb')
            ->where('pcb.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandsByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('b')
            ->from(PromoCodeBrand::class, 'pcb')
            ->join(Brand::class, 'b', Join::WITH, 'pcb.brand = b')
            ->where('pcb.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return int[]
     */
    public function getProductIdsFromBrandsByPromoCodeId(int $promoCodeId): array
    {
        $result = $this->getQueryBuilder()
            ->select('p.id')
            ->from(PromoCodeBrand::class, 'pcb')
            ->join(Product::class, 'p', Join::WITH, 'pcb.brand = p.brand')
            ->where('pcb.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();

        return array_column($result, 'id');
    }
}
