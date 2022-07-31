<?php

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class HeurekaProductDomainRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        EntityManagerInterface $em
    ) {
        $this->em = $em;
    }

    /**
     * @param int $productId
     * @param int $domainId
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain|null
     */
    public function findByProductIdAndDomainId(int $productId, int $domainId): ?HeurekaProductDomain
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(HeurekaProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->andWhere('p.domainId = :domainId')
            ->setParameter('productId', $productId)
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $productId
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]
     */
    public function findByProductId(int $productId): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(HeurekaProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->setParameter('productId', $productId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @param int[] $productsIds
     * @param int $domainId
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]
     */
    public function getHeurekaProductDomainsByProductsIdsDomainIdIndexedByProductId(array $productsIds, int $domainId): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(HeurekaProductDomain::class, 'p')
            ->where('p.domainId = :domainId')
            ->andWhere('p.product IN (:productIds)')
            ->setParameter('productIds', $productsIds)
            ->setParameter('domainId', $domainId);

        $result = $queryBuilder->getQuery()->execute();

        $indexedResult = [];
        foreach ($result as $heurekaProductDomain) {
            $productId = $heurekaProductDomain->getProduct()->getId();
            $indexedResult[$productId] = $heurekaProductDomain;
        }

        return $indexedResult;
    }
}
