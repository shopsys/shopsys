<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Override;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory as BaseFilterQueryFactory;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade, \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategory(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, \App\Model\Category\Category $category)
 * @method \App\Model\Product\Search\FilterQuery createWithProductFilterData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByBrand(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, \App\Model\Product\Brand\Brand $brand)
 * @method \App\Model\Product\Search\FilterQuery createListable()
 * @method \App\Model\Product\Search\FilterQuery createVisible()
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategoryWithPriceAndStockFilter(\App\Model\Category\Category $category, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByBrandIdWithPriceAndStockFilter(int $brandId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsWithPriceAndStockFilter(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsBySearchTextWithPriceAndStockFilter(string $searchText, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery addPricesAndStockFromFilterDataToQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, \App\Model\Product\Search\FilterQuery $filterQuery)
 * @method \App\Model\Product\Search\FilterQuery createVisibleProductsByProductIdsFilter(int[] $productIds)
 * @method \App\Model\Product\Search\FilterQuery createSellableProductsByProductIdsFilter(int[] $productIds, int|null $limit = null)
 * @method \App\Model\Product\Search\FilterQuery createVisibleProductsByProductUuidsFilter(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery createSellableProductsByProductUuidsFilter(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery createListableWithProductFilter(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \App\Model\Product\Search\FilterQuery createOnlyExistingProductIdsFilterQuery(int[] $productIds, int $domainId)
 * @method \App\Model\Product\Search\FilterQuery createSellableProductIdsByProductUuidsFilter(string[] $productUuids)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByFlagIdWithPriceAndStockFilter(int $flagId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \App\Model\Product\Search\FilterQuery createVisibleForCategory(\App\Model\Category\Category $category)
 * @method \App\Model\Product\Search\FilterQuery filterByCategory(\App\Model\Product\Search\FilterQuery $filterQuery, \App\Model\Category\Category $category)
 */
class FilterQueryFactory extends BaseFilterQueryFactory
{
    /**
     * @param string $indexName
     * @return \App\Model\Product\Search\FilterQuery
     */
    #[Override]
    public function create(string $indexName): BaseFilterQuery
    {
        return new FilterQuery($indexName, $this->pricingSetting->getSellingPriceType());
    }
}
