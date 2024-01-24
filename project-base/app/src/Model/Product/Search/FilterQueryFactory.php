<?php

declare(strict_types=1);

namespace App\Model\Product\Search;

use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery as BaseFilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory as BaseFilterQueryFactory;

/**
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @method __construct(\App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategoryId(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery createWithProductFilterData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByBrandId(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $brandId)
 * @method \App\Model\Product\Search\FilterQuery createListable()
 * @method \App\Model\Product\Search\FilterQuery createVisible()
 * @method \App\Model\Product\Search\FilterQuery createListableProductsByCategoryIdWithPriceAndStockFilter(int $categoryId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData)
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param string $searchText
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function createListableProductsBySearchText(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        string $searchText,
    ): BaseFilterQuery {
        /** @var \App\Model\Product\Search\FilterQuery $filterQuery */
        $filterQuery = parent::createListableProductsBySearchText($productFilterData, $orderingModeId, $page, $limit, $searchText);

        $filterQuery = $filterQuery->filterNotExcludeOrInStock();

        return $filterQuery;
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByFlagIdWithPriceAndStockFilter(
        int $flagId,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->filterByFlags([$flagId]);
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \App\Model\Product\Search\FilterQuery
     */
    public function createListableProducts(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
    ): FilterQuery {
        return $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit);
    }
}
