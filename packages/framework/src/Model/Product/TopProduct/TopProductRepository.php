<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class TopProductRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        protected readonly ProductRepository $productRepository,
    ) {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getTopProductRepository()
    {
        return $this->em->getRepository(TopProduct::class);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\TopProduct\TopProduct[]
     */
    public function getAll($domainId)
    {
        return $this->getTopProductRepository()->findBy(['domainId' => $domainId], ['position' => 'ASC']);
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProductsForTopProductsOnDomain($domainId, $pricingGroup)
    {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);

        $queryBuilder
            ->join(TopProduct::class, 'tp', Join::WITH, 'tp.product = p')
            ->andWhere('tp.domainId = :domainId')
            ->andWhere('tp.domainId = prv.domainId')
            ->orderBy('tp.position')
            ->setParameter('domainId', $domainId);

        return $queryBuilder->getQuery()->execute();
    }
}
