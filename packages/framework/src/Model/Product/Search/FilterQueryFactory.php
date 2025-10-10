<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class FilterQueryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Category\AutomatedFilter\CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     */
    public function __construct(
        protected readonly ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
        protected readonly CategoryAutomatedFilterFacade $categoryAutomatedFilterFacade,
        protected readonly PricingSetting $pricingSetting,
    ) {
    }

    /**
     * @param string $indexName
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function create(string $indexName): FilterQuery
    {
        return new FilterQuery($indexName, $this->pricingSetting->getSellingPriceType());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByCategory(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        Category $category,
    ): FilterQuery {
        $filterQuery = $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit);

        return $this->filterByCategory($filterQuery, $category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createWithProductFilterData(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
    ): FilterQuery {
        return $this->createListableWithProductFilter($productFilterData)
            ->setPage($page)
            ->setLimit($limit)
            ->applyOrdering($orderingModeId, $this->currentCustomerUser->getPricingGroup());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByBrand(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        Brand $brand,
    ): FilterQuery {
        return $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit)
            ->filterByBrands([$brand->getId()]);
    }

    /**
     * @return string
     * @internal visibility of this method will be changed to protected in next major version
     */
    public function getIndexName(): string
    {
        return $this->indexDefinitionLoader->getIndexDefinition(
            ProductIndex::getName(),
            $this->domain->getId(),
        )->getIndexAlias();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListable(): FilterQuery
    {
        return $this->createVisible()
            ->filterOnlySellable()
            ->filterOutVariants();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createVisible(): FilterQuery
    {
        return $this->create($this->getIndexName())
            ->filterOnlyVisible($this->currentCustomerUser->getPricingGroup());
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByCategoryWithPriceAndStockFilter(
        Category $category,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->filterByCategory($this->createListable(), $category);

        return $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);
    }

    /**
     * @param int $brandId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByBrandIdWithPriceAndStockFilter(
        int $brandId,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->filterByBrands([$brandId]);
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsWithPriceAndStockFilter(ProductFilterData $productFilterData): FilterQuery
    {
        $filterQuery = $this->createListable();
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsBySearchTextWithPriceAndStockFilter(
        string $searchText,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->search($searchText);
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function addPricesAndStockFromFilterDataToQuery(
        ProductFilterData $productFilterData,
        FilterQuery $filterQuery,
    ): FilterQuery {
        $filterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery(
            $productFilterData,
            $filterQuery,
            $this->currentCustomerUser->getPricingGroup(),
        );
        $filterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param int[] $productIds
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createVisibleProductsByProductIdsFilter(array $productIds): FilterQuery
    {
        return $this->createVisible()
            ->filterByProductIds($productIds)
            ->applyOrderingByIdsArray($productIds);
    }

    /**
     * @param int[] $productIds
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByProductIdsFilter(array $productIds, ?int $limit = null): FilterQuery
    {
        $filterQuery = $this->createListable()
            ->filterByProductIds($productIds)
            ->applyOrderingByIdsArray($productIds);

        if ($limit === null) {
            return $filterQuery;
        }

        return $filterQuery->setLimit($limit);
    }

    /**
     * @param int[] $productIds
     * @param int|null $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createSellableProductsByProductIdsFilter(array $productIds, ?int $limit = null): FilterQuery
    {
        $filterQuery = $this
            ->createVisibleProductsByProductIdsFilter($productIds)
            ->filterOnlySellable();

        if ($limit === null) {
            return $filterQuery;
        }

        return $filterQuery->setLimit($limit);
    }

    /**
     * @param string[] $productUuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createVisibleProductsByProductUuidsFilter(array $productUuids): FilterQuery
    {
        return $this->createVisible()
            ->filterByProductUuids($productUuids);
    }

    /**
     * @param string[] $productUuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createSellableProductsByProductUuidsFilter(array $productUuids): FilterQuery
    {
        return $this->createVisibleProductsByProductUuidsFilter($productUuids)
            ->filterOnlySellable();
    }

    /**
     * @param string[] $productUuids
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createSellableProductIdsByProductUuidsFilter(array $productUuids): FilterQuery
    {
        return $this->createSellableProductsByProductUuidsFilter($productUuids)
            ->restrictFields(['id']);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableWithProductFilter(ProductFilterData $productFilterData): FilterQuery
    {
        $filterQuery = $this->createListable();
        $filterQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
            $productFilterData,
            $filterQuery,
        );
        $filterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery(
            $productFilterData,
            $filterQuery,
            $this->currentCustomerUser->getPricingGroup(),
        );

        return $filterQuery;
    }

    /**
     * @param int[] $productIds
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createOnlyExistingProductIdsFilterQuery(array $productIds, int $domainId): FilterQuery
    {
        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ProductIndex::getName(), $domainId);

        return $this->create($indexDefinition->getIndexAlias())
            ->filterByProductIds($productIds)
            ->restrictFields(['id']);
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByFlagIdWithPriceAndStockFilter(
        int $flagId,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->filterByFlags([$flagId]);

        return $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createVisibleForCategory(Category $category): FilterQuery
    {
        return $this->filterByCategory($this->createVisible(), $category);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery $filterQuery
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    protected function filterByCategory(FilterQuery $filterQuery, Category $category): FilterQuery
    {
        return $this->categoryAutomatedFilterFacade->applyFiltersByCategory($filterQuery, $category);
    }
}
