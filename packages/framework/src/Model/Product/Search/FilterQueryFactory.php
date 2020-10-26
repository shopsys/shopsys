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
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer
     */
    protected $productFilterDataToQueryTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     */
    protected $indexDefinitionLoader;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        CurrentCustomerUser $currentCustomerUser,
        IndexDefinitionLoader $indexDefinitionLoader,
        Domain $domain
    ) {
        $this->productFilterDataToQueryTransformer = $productFilterDataToQueryTransformer;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
        $this->domain = $domain;
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
        int $categoryId
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
        int $limit
    ): FilterQuery {
        $filterQuery = $this->createListable()
            ->setPage($page)
            ->setLimit($limit)
            ->applyOrdering($orderingModeId, $this->currentCustomerUser->getPricingGroup());

        $filterQuery = $this->productFilterDataToQueryTransformer->addBrandsToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addFlagsToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addParametersToQuery(
            $productFilterData,
            $filterQuery
        );
        $filterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $filterQuery);
        $filterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery(
            $productFilterData,
            $filterQuery,
            $this->currentCustomerUser->getPricingGroup()
        );

        return $filterQuery;
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
        int $brandId
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
        string $searchText
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
            $this->domain->getId()
        )->getIndexAlias();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListable(): FilterQuery
    {
        return $this->create($this->getIndexName())
            ->filterOnlySellable()
            ->filterOnlyVisible($this->currentCustomerUser->getPricingGroup());
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsByCategoryIdWithPriceAndStockFilter(int $categoryId, ProductFilterData $productFilterData): FilterQuery
    {
        $filterQuery = $this->createListable()
            ->filterByCategory([$categoryId]);
        $filterQuery = $this->addPricesAndStockFromFilterDataToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param string $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createListableProductsBySearchTextWithPriceAndStockFilter(string $searchText, ProductFilterData $productFilterData): FilterQuery
    {
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
    public function addPricesAndStockFromFilterDataToQuery(ProductFilterData $productFilterData, FilterQuery $filterQuery): FilterQuery
    {
        $filterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery(
            $productFilterData,
            $filterQuery,
            $this->currentCustomerUser->getPricingGroup()
        );
        $filterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $filterQuery);

        return $filterQuery;
    }

    /**
     * @param int $productId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     */
    public function createVisibleProductsByProductIdFilter(int $productId): FilterQuery
    {
        return $this->createListable()->filterByProductId($productId);
    }
}
