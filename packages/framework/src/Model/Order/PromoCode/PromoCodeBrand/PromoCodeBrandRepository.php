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
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder();
    }

    /**
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
            ->getResult();
    }

    /**
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
            ->getResult();
    }

    /**
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
            ->getResult();

        return array_column($result, 'id');
    }
}
