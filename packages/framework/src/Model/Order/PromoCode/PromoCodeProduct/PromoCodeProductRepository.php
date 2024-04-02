<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Product\Product;

class PromoCodeProductRepository
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
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProduct[]
     */
    public function getAllByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('pcp')
            ->from(PromoCodeProduct::class, 'pcp')
            ->where('pcp.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getProductsByPromoCodeId(int $promoCodeId): array
    {
        return $this->getQueryBuilder()
            ->select('p')
            ->from(PromoCodeProduct::class, 'pcc')
            ->join(Product::class, 'p', Join::WITH, 'pcc.product = p')
            ->where('pcc.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();
    }

    /**
     * @param int $promoCodeId
     * @return int[]
     */
    public function getProductIdsByPromoCodeId(int $promoCodeId): array
    {
        $result = $this->getQueryBuilder()
            ->select('p.id')
            ->from(PromoCodeProduct::class, 'pcc')
            ->join('pcc.product', 'p')
            ->where('pcc.promoCode = :promoCodeId')
            ->setParameter('promoCodeId', $promoCodeId)
            ->getQuery()
            ->execute();

        return array_column($result, 'id');
    }
}
