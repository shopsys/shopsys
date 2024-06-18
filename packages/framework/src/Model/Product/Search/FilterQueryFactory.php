<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Search;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;

class FilterQueryFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly IndexDefinitionLoader $indexDefinitionLoader,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param string $indexName
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function create(string $indexName): FilterQuery
    {
        return new FilterQuery($indexName);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByCategoryId(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $categoryId,
    ): FilterQuery {
        return $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit)
            ->filterByCategory([$categoryId]);
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
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByBrandId(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $brandId,
    ): FilterQuery {
        return $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit)
            ->filterByBrands([$brandId]);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsBySearchText(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        string $searchText,
    ): FilterQuery {
        return $this->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit)
            ->search($searchText);
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
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByCategoryIdWithPriceAndStockFilter(
        int $categoryId,
        ProductFilterData $productFilterData,
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->filterByCategory([$categoryId]);
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
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
            ->filterByProductIds($productIds);
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
            ->filterOnlySellable()
            ->applyOrderingByIdsArray($productIds);

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
}
