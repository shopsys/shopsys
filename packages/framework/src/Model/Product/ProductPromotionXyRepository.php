<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductPromotionXyRepository
{
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductPromotionXy::class);
    }

    protected function getFlagRepository(): EntityRepository
    {
        return $this->em->getRepository(Flag::class);
    }

    public function findFlagByQuantities(int $buyQuantity, int $freeQuantity): ?Flag
    {
        $queryBuilder = $this->getFlagRepository()->createQueryBuilder('f')
            ->select('f')
            ->join('f.promotionXy', 'pxy')
            ->where('pxy.buyQuantity = :buyQuantity')
            ->andWhere('pxy.freeQuantity = :freeQuantity')
            ->setParameter('buyQuantity', $buyQuantity)
            ->setParameter('freeQuantity', $freeQuantity)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    public function findPromotionXyByQuantities(int $buyQuantity, int $freeQuantity): ?ProductPromotionXy
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('pxy')
            ->select('pxy')
            ->where('pxy.buyQuantity = :buyQuantity')
            ->andWhere('pxy.freeQuantity = :freeQuantity')
            ->setParameter('buyQuantity', $buyQuantity)
            ->setParameter('freeQuantity', $freeQuantity)
            ->setMaxResults(1);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * @return int[]
     */
    public function getAllProductIdsWithPromotionXy(): array
    {
        $result = $this->em->getRepository(ProductDomain::class)
            ->createQueryBuilder('pd')
            ->select('DISTINCT IDENTITY(pd.product) AS productId')
            ->join('pd.promotionXy', 'pxy')
            ->getQuery()
            ->getArrayResult();

        return array_column($result, 'productId');
    }
}
