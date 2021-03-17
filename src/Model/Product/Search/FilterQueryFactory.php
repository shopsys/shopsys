<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory as BaseFilterQueryFactory;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategoryId(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery createWithProductFilterData(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByBrandId(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $brandId)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsBySearchText(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, string $searchText)
 * @method \App\Model\Product\Search\FilterQuery createListable()
 * @method \App\Model\Product\Search\FilterQuery createVisible()
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategoryIdWithPriceAndStockFilter(int $categoryId, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByBrandIdWithPriceAndStockFilter(int $brandId, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsWithPriceAndStockFilter(\App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsBySearchTextWithPriceAndStockFilter(string $searchText, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery addPricesAndStockFromFilterDataToQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery createVisibleProductsByProductIdsFilter(int[] $productIds)
 * @method \App\Model\Product\Search\FilterQuery createSellableProductsByProductIdsFilter(int[] $productIds, int|null $limit)
 * @method \App\Model\Product\Search\FilterQuery createVisibleProductsByProductUuidsFilter(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery createSellableProductsByProductUuidsFilter(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery createListableWithProductFilter(\App\Model\Product\Filter\ProductFilterData $productFilterData)
 */
class FilterQueryFactory extends BaseFilterQueryFactory
{
    /**
     * @param string $indexName
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function create(string $indexName): BaseFilterQuery
    {
        return new FilterQuery($indexName);
    }
}
