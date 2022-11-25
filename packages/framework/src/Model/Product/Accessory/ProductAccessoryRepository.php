<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductAccessoryRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender
     */
    protected $queryBuilderExtender;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
     */
    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        QueryBuilderExtender $queryBuilderExtender
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->queryBuilderExtender = $queryBuilderExtender;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getProductAccessoryRepository(): EntityRepository
    {
        return $this->em->getRepository(ProductAccessory::class);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getTopOfferedAccessories(Product $product, int $domainId, PricingGroup $pricingGroup, ?int $limit): array
    {
        $queryBuilder = $this->getAllOfferedAccessoriesByProductQueryBuilder($product, $domainId, $pricingGroup);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return object[]
     */
    public function getAllByProduct(Product $product): array
    {
        return $this->getProductAccessoryRepository()->findBy(['product' => $product], ['position' => 'asc']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedAccessoriesByProduct(Product $product, int $domainId, PricingGroup $pricingGroup): array
    {
        return $this->getAllOfferedAccessoriesByProductQueryBuilder($product, $domainId, $pricingGroup)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getAllOfferedAccessoriesByProductQueryBuilder(Product $product, int $domainId, PricingGroup $pricingGroup): QueryBuilder
    {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $this->queryBuilderExtender->addOrExtendJoin(
            $queryBuilder,
            ProductAccessory::class,
            'pa',
            'pa.accessory = p AND pa.product = :product'
        );
        $queryBuilder
            ->setParameter('product', $product)
            ->orderBy('pa.position', 'ASC');

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $accessory
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory|null
     */
    public function findByProductAndAccessory(Product $product, Product $accessory): ?ProductAccessory
    {
        return $this->getProductAccessoryRepository()->findOneBy([
            'product' => $product,
            'accessory' => $accessory,
        ]);
    }
}
