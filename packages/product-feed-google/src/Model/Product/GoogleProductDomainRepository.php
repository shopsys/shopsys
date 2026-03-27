<?php

declare(strict_types=1);

namespace Shopsys\ProductFeed\GoogleBundle\Model\Product;

use Doctrine\ORM\EntityManagerInterface;

class GoogleProductDomainRepository
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return \Shopsys\ProductFeed\GoogleBundle\Model\Product\GoogleProductDomain[]
     */
    public function findByProductId(int $productId): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(GoogleProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->setParameter('productId', $productId);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findByProductIdAndDomainId(
        int $productId,
        int $domainId,
    ): ?GoogleProductDomain {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('p')
            ->from(GoogleProductDomain::class, 'p')
            ->where('p.product = :productId')
            ->andWhere('p.domainId = :domainId')
            ->setParameter('productId', $productId)
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()
            ->getOneOrNullResult();
    }
}
