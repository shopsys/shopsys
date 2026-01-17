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
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getOfferedAccessories(
        Product $product,
        int $domainId,
        PricingGroup $pricingGroup,
        ?int $limit = null,
    ) {
        $queryBuilder = $this->getAllOfferedAccessoriesByProductQueryBuilder($product, $domainId, $pricingGroup);
        $queryBuilder->setMaxResults($limit);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessory[]
     */
    public function getAllByProduct(Product $product)
    {
        return $this->getProductAccessoryRepository()->findBy(['product' => $product], ['position' => 'asc']);
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getAllOfferedAccessoriesByProduct(Product $product, $domainId, PricingGroup $pricingGroup)
    {
        return $this->getAllOfferedAccessoriesByProductQueryBuilder($product, $domainId, $pricingGroup)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param int $domainId
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
