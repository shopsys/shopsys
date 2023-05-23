<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductAccessoryRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender $queryBuilderExtender
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ProductRepository $productRepository,
        protected readonly QueryBuilderExtender $queryBuilderExtender,
    ) {
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getProductAccessoryRepository()
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
    public function getTopOfferedAccessories(Product $product, $domainId, PricingGroup $pricingGroup, $limit)
    {
        $queryBuilder = $this->getAllOfferedAccessoriesByProductQueryBuilder($product, $domainId, $pricingGroup);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory[]
     */
    public function getAllByProduct(Product $product)
    {
        return $this->getProductAccessoryRepository()->findBy(['product' => $product], ['position' => 'asc']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedAccessoriesByProduct(Product $product, $domainId, PricingGroup $pricingGroup)
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
    protected function getAllOfferedAccessoriesByProductQueryBuilder(
        Product $product,
        $domainId,
        PricingGroup $pricingGroup,
    ) {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $this->queryBuilderExtender->addOrExtendJoin(
            $queryBuilder,
            ProductAccessory::class,
            'pa',
            'pa.accessory = p AND pa.product = :product',
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
    public function findByProductAndAccessory(Product $product, Product $accessory)
    {
        return $this->getProductAccessoryRepository()->findOneBy([
            'product' => $product,
            'accessory' => $accessory,
        ]);
    }
}
