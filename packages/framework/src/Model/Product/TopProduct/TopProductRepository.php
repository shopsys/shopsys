<?php

namespace Shopsys\FrameworkBundle\Model\Product\TopProduct;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class TopProductRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    public function __construct(EntityManagerInterface $entityManager, ProductRepository $productRepository)
    {
        $this->em = $entityManager;
        $this->productRepository = $productRepository;
    }

    protected function getTopProductRepository(): \Doctrine\ORM\EntityRepository
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
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedProductsForTopProductsOnDomain(int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup): array
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
