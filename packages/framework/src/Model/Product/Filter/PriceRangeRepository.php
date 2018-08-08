<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductCalculatedPrice;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class PriceRangeRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderService
     */
    private $queryBuilderService;

    public function __construct(ProductRepository $productRepository, QueryBuilderService $queryBuilderService)
    {
        $this->productRepository = $productRepository;
        $this->queryBuilderService = $queryBuilderService;
    }
    
    public function getPriceRangeInCategory(int $domainId, PricingGroup $pricingGroup, Category $category): \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
    {
        $productsQueryBuilder = $this->productRepository->getListableInCategoryQueryBuilder(
            $domainId,
            $pricingGroup,
            $category
        );

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    /**
     * @param string|null $searchText
     */
    public function getPriceRangeForSearch(int $domainId, PricingGroup $pricingGroup, string $locale, ?string $searchText): \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
    {
        $productsQueryBuilder = $this->productRepository
            ->getListableBySearchTextQueryBuilder($domainId, $pricingGroup, $locale, $searchText);

        return $this->getPriceRangeByProductsQueryBuilder($productsQueryBuilder, $pricingGroup);
    }

    private function getPriceRangeByProductsQueryBuilder(QueryBuilder $productsQueryBuilder, PricingGroup $pricingGroup): \Shopsys\FrameworkBundle\Model\Product\Filter\PriceRange
    {
        $queryBuilder = clone $productsQueryBuilder;

        $this->queryBuilderService
            ->addOrExtendJoin($queryBuilder, ProductCalculatedPrice::class, 'pcp', 'pcp.product = p')
            ->andWhere('pcp.pricingGroup = :pricingGroup')
            ->setParameter('pricingGroup', $pricingGroup)
            ->resetDQLPart('groupBy')
            ->resetDQLPart('orderBy')
            ->select('MIN(pcp.priceWithVat) AS minimalPrice, MAX(pcp.priceWithVat) AS maximalPrice');

        $priceRangeData = $queryBuilder->getQuery()->execute();
        $priceRangeDataRow = reset($priceRangeData);

        return new PriceRange($priceRangeDataRow['minimalPrice'], $priceRangeDataRow['maximalPrice']);
    }
}
