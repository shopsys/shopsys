<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\HeurekaBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class HeurekaProductDomainRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

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
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]|null
     */
    public function findByProductId(int $productId): ?array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(HeurekaProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->setParameter('productId', $productId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\ProductFeed\HeurekaBundle\Model\Product\HeurekaProductDomain[]
     */
    public function getHeurekaProductDomainsByProductsIdsDomainIdIndexedByProductId(
        array $productsIds,
        int $domainId,
    ): array {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(HeurekaProductDomain::class, 'p')
            ->where('p.domainId = :domainId')
            ->andWhere('p.product IN (:productIds)')
            ->setParameter('productIds', $productsIds)
            ->setParameter('domainId', $domainId);

        $result = $queryBuilder->getQuery()->getResult();

        $indexedResult = [];

        foreach ($result as $heurekaProductDomain) {
            $productId = $heurekaProductDomain->getProduct()->getId();
            $indexedResult[$productId] = $heurekaProductDomain;
        }

        return $indexedResult;
    }
}
