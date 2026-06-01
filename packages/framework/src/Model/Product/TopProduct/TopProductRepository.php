<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class TopProductRepository
{
    protected EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $entityManager,
        protected readonly ProductRepository $productRepository,
    ) {
        $this->em = $entityManager;
    }

    protected function getTopProductRepository(): EntityRepository
    {
        return $this->em->getRepository(TopProduct::class);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll(int $domainId): array
    {
        return $this->getTopProductRepository()->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @return array<int, int>
     */
    public function getTopProductPositionsIndexedByProductIdForDomain(int $domainId): array
    {
        $rows = $this->em->createQueryBuilder()
            ->select('IDENTITY(tp.product) AS productId', 'tp.position AS position')
            ->from(TopProduct::class, 'tp')
            ->andWhere('tp.domainId = :domainId')
            ->setParameter('domainId', $domainId)
            ->getQuery()
            ->getArrayResult();

        $positions = [];

        foreach ($rows as $row) {
            $positions[(int)$row['productId']] = $row['position'];
        }

        return $positions;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProductsForTopProductsOnDomain(
        int $domainId,
        PricingGroup $pricingGroup,
        ?int $limit = null,
    ): array {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);

        $queryBuilder
            ->join(TopProduct::class, 'tp', Join::WITH, 'tp.product = p')
            ->andWhere('tp.domainId = :domainId')
            ->andWhere('tp.domainId = prv.domainId')
            ->orderBy('tp.position')
            ->setParameter('domainId', $domainId)
            ->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }
}
