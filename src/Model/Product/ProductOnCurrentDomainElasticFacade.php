<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository, \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository, \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsInCategoryFilterQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsForBrandFilterQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $brandId)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsForSearchTextFilterQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, string|null $searchText)
 * @method \App\Model\Product\Search\FilterQuery createFilterQueryWithProductFilterData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method array getProductsByCategory(\App\Model\Category\Category $category, int $limit, int $offset, string $orderingModeId)
 */
class ProductOnCurrentDomainElasticFacade extends BaseProductOnCurrentDomainElasticFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param int $categoryId
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedProductsInCategoryExcludeProduct(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $categoryId,
        int $productId
    ): PaginationResult {
        $filterQuery = $this->createListableProductsInCategoryFilterQuery($productFilterData, $orderingModeId, $page, $limit, $categoryId);
        /** @var \App\Model\Product\Search\FilterQuery $filterQuery */
        $filterQuery = $filterQuery->excludeProductByProductId($productId);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }
}
