<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\BestsellingProduct;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ManualBestsellingProductRepository
{
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $entityManager,
        protected readonly ProductRepository $productRepository,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct[]
     */
    public function getByCategory(int $domainId, Category $category): array
    {
        $queryBuilder = $this->em->createQueryBuilder()
            ->select('bp')
            ->from(ManualBestsellingProduct::class, 'bp', 'bp.position')
            ->where('bp.category = :category')
            ->andWhere('bp.domainId = :domainId')
            ->setParameter('category', $category)
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProduct[]
     */
    public function getOfferedByCategory(int $domainId, Category $category, PricingGroup $pricingGroup): array
    {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);

        $queryBuilder
            ->select('bp')
            ->join(ManualBestsellingProduct::class, 'bp', Join::WITH, 'bp.product = p')
            ->andWhere('bp.category = :category')
            ->andWhere('bp.domainId = prv.domainId')
            ->setParameter('category', $category);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return int[]
     */
    public function getCountsIndexedByCategoryId(int $domainId): array
    {
        $queryBuilder = $this->em->createQueryBuilder();

        $queryBuilder
            ->select('c.id, COUNT(mbp) AS cnt')
            ->from(Category::class, 'c')
            ->leftJoin(
                ManualBestsellingProduct::class,
                'mbp',
                Join::WITH,
                'mbp.category = c AND mbp.domainId = :domainId',
            )
            ->setParameter('domainId', $domainId)
            ->groupBy('c.id');

        $rows = $queryBuilder->getQuery()->getResult();
        $countsIndexedByCategoryId = [];

        foreach ($rows as $row) {
            $countsIndexedByCategoryId[$row['id']] = $row['cnt'];
        }

        return $countsIndexedByCategoryId;
    }
}
