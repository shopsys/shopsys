<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\ZboziBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class ZboziProductDomainRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function findByProductIdAndDomainId(
        int $productId,
        int $domainId,
    ): ?ZboziProductDomain {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(ZboziProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->andWhere('p.domainId = :domainId')
            ->setParameter('productId', $productId)
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[]|null
     */
    public function findByProductId(int $productId): ?array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(ZboziProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->setParameter('productId', $productId);

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return \Shopsys\ProductFeed\ZboziBundle\Model\Product\ZboziProductDomain[]
     */
    public function getZboziProductDomainsByProductsIdsDomainIdIndexedByProductId(
        array $productsIds,
        int $domainId,
    ): array {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(ZboziProductDomain::class, 'p')
            ->where('p.domainId = :domainId')
            ->andWhere('p.product IN (:productIds)')
            ->setParameter('productIds', $productsIds)
            ->setParameter('domainId', $domainId);

        $result = $queryBuilder->getQuery()->execute();

        $indexedResult = [];

        foreach ($result as $zboziProductDomain) {
            $productId = $zboziProductDomain->getProduct()->getId();
            $indexedResult[$productId] = $zboziProductDomain;
        }

        return $indexedResult;
    }
}
