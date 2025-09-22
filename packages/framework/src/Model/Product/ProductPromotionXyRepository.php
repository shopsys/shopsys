<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;

class ProductPromotionXyRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(protected readonly EntityManagerInterface $em)
    {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductPromotionXy::class);
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getFlagRepository(): EntityRepository
    {
        return $this->em->getRepository(Flag::class);
    }

    /**
     * @param int $buyQuantity
     * @param int $freeQuantity
     * @return \Shopsys\FrameworkBundle\Model\Product\Flag\Flag|null
     */
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

    /**
     * @param int $buyQuantity
     * @param int $freeQuantity
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductPromotionXy|null
     */
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
}
