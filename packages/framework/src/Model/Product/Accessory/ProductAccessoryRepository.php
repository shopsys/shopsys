<?php

namespace Shopsys\FrameworkBundle\Model\Product\Accessory;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService;
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
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService
     */
    protected $queryBuilderService;

    public function __construct(
        EntityManagerInterface $em,
        ProductRepository $productRepository,
        QueryBuilderService $queryBuilderService
    ) {
        $this->em = $em;
        $this->productRepository = $productRepository;
        $this->queryBuilderService = $queryBuilderService;
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    public function getProductAccessoryRepository()
    {
        return $this->em->getRepository(ProductAccessory::class);
    }

    /**
     * @param int $domainId
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getTopOfferedAccessories(Product $product, $domainId, PricingGroup $pricingGroup, $limit)
    {
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
    protected function getAllOfferedAccessoriesByProductQueryBuilder(Product $product, $domainId, PricingGroup $pricingGroup)
    {
        $queryBuilder = $this->productRepository->getAllOfferedQueryBuilder($domainId, $pricingGroup);
        $this->queryBuilderService->addOrExtendJoin(
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
