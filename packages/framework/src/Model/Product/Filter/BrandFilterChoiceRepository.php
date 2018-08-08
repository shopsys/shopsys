<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class BrandFilterChoiceRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    public function __construct(
        ProductRepository $productRepository
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandFilterChoicesInCategory(int $domainId, PricingGroup $pricingGroup, Category $category): array
    {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category
        );

        return $this->getBrandsByProductsQueryBuilder($productsQueryBuilder);
    }

    /**
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    public function getBrandFilterChoicesForSearch(int $domainId, PricingGroup $pricingGroup, string $locale, ?string $searchText): array
    {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getBrandsByProductsQueryBuilder($productsQueryBuilder);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Brand\Brand[]
     */
    private function getBrandsByProductsQueryBuilder(QueryBuilder $productsQueryBuilder): array
    {
        $clonedProductsQueryBuilder = clone $productsQueryBuilder;

        $clonedProductsQueryBuilder
            ->select('1')
            ->join('p.brand', 'pb')
            ->andWhere('pb.id = b.id')
            ->resetDQLPart('orderBy');

        $brandsQueryBuilder = $productsQueryBuilder->getEntityManager()->createQueryBuilder();
        $brandsQueryBuilder
            ->select('b')
            ->from(Brand::class, 'b')
            ->andWhere($brandsQueryBuilder->expr()->exists($clonedProductsQueryBuilder))
            ->orderBy('b.name', 'asc');

        foreach ($clonedProductsQueryBuilder->getParameters() as $parameter) {
            $brandsQueryBuilder->setParameter($parameter->getName(), $parameter->getValue());
        }

        return $brandsQueryBuilder->getQuery()->execute();
    }
}
