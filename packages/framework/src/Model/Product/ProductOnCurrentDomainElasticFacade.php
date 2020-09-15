<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer;

class ProductOnCurrentDomainElasticFacade implements ProductOnCurrentDomainFacadeInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    protected $currentCustomerUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository
     */
    protected $productAccessoryRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository
     */
    protected $productElasticsearchRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository
     */
    protected $productFilterCountDataElasticsearchRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer
     * @deprecated this property will be removed in next major version
     */
    protected $productFilterDataToQueryTransformer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
     */
    protected $filterQueryFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader
     * @deprecated this property will be removed in next major version
     */
    protected $indexDefinitionLoader;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     */
    public function __construct(
        ProductRepository $productRepository,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        ProductAccessoryRepository $productAccessoryRepository,
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer,
        FilterQueryFactory $filterQueryFactory,
        IndexDefinitionLoader $indexDefinitionLoader
    ) {
        $this->productRepository = $productRepository;
        $this->domain = $domain;
        $this->currentCustomerUser = $currentCustomerUser;
        $this->productAccessoryRepository = $productAccessoryRepository;
        $this->productElasticsearchRepository = $productElasticsearchRepository;
        $this->productFilterCountDataElasticsearchRepository = $productFilterCountDataElasticsearchRepository;
        $this->productFilterDataToQueryTransformer = $productFilterDataToQueryTransformer;
        $this->filterQueryFactory = $filterQueryFactory;
        $this->indexDefinitionLoader = $indexDefinitionLoader;
    }

    /**
     * {@inheritdoc}
     */
    public function getVisibleProductById(int $productId): Product
    {
        return $this->productRepository->getVisible(
            $productId,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getAccessoriesForProduct(Product $product): array
    {
        return $this->productAccessoryRepository->getAllOfferedAccessoriesByProduct(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getVariantsForProduct(Product $product): array
    {
        return $this->productRepository->getAllSellableVariantsByMainVariant(
            $product,
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedProductsInCategory(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $categoryId
    ): PaginationResult {
        $filterQuery = $this->filterQueryFactory->createListableProductsInCategory($productFilterData, $orderingModeId, $page, $limit, $categoryId);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedProductsForBrand(string $orderingModeId, int $page, int $limit, int $brandId): PaginationResult
    {
        $emptyProductFilterData = new ProductFilterData();

        $filterQuery = $this->filterQueryFactory->createListableProductsForBrand($emptyProductFilterData, $orderingModeId, $page, $limit, $brandId);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginatedProductsForSearch(
        string $searchText,
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit
    ): PaginationResult {
        $filterQuery = $this->filterQueryFactory->createListableProductsForSearchText($productFilterData, $orderingModeId, $page, $limit, $searchText);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchAutocompleteProducts(?string $searchText, int $limit): PaginationResult
    {
        $emptyProductFilterData = new ProductFilterData();
        $page = 1;

        $filterQuery = $this->filterQueryFactory->createListableProductsForSearchText($emptyProductFilterData, ProductListOrderingConfig::ORDER_BY_RELEVANCE, $page, $limit, $searchText);

        $productIds = $this->productElasticsearchRepository->getSortedProductIdsByFilterQuery($filterQuery);

        $listableProductsByIds = $this->productRepository->getListableByIds($this->domain->getId(), $this->currentCustomerUser->getPricingGroup(), $productIds->getIds());

        return new PaginationResult($page, $limit, $productIds->getTotal(), $listableProductsByIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductFilterCountDataInCategory(
        int $categoryId,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData
    ): ProductFilterCountData {
        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $this->filterQueryFactory->createProductFilterCount($categoryId, $productFilterData)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getProductFilterCountDataForSearch(
        ?string $searchText,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData
    ): ProductFilterCountData {
        $searchText = $searchText ?? '';

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInSearch(
            $productFilterData,
            $this->filterQueryFactory->createProductSearchCount($searchText, $productFilterData)
        );
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     */
    public function getProductsOnCurrentDomain(int $limit, int $offset, string $orderingModeId): array
    {
        $emptyProductFilterData = new ProductFilterData();
        $filterQuery = $this->filterQueryFactory->createWithProductFilterData(
            $emptyProductFilterData,
            $orderingModeId,
            1,
            $limit
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);
        return $productsResult->getHits();
    }

    /**
     * @return int
     */
    public function getProductsCountOnCurrentDomain(): int
    {
        $filterQuery = $this->filterQueryFactory->createSellableAndVisible();

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     */
    public function getProductsByCategory(Category $category, int $limit, int $offset, string $orderingModeId): array
    {
        $emptyProductFilterData = new ProductFilterData();
        $filterQuery = $this->filterQueryFactory->createListableProductsInCategory(
            $emptyProductFilterData,
            $orderingModeId,
            1,
            $limit,
            $category->getId()
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);
        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param int $categoryId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     * @deprecated This method will be removed in next major version. Use \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterQueryFactory::createListableProductsInCategory() instead.
     */
    protected function createListableProductsInCategoryFilterQuery(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $categoryId
    ): FilterQuery {
        return $this->filterQueryFactory->createListableProductsInCategory($productFilterData, $orderingModeId, $page, $limit, $categoryId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     * @deprecated This method will be removed in next major version. Use \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterQueryFactory::createListableProductsForBrand() instead.
     */
    protected function createListableProductsForBrandFilterQuery(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        int $brandId
    ): FilterQuery {
        return $this->filterQueryFactory->createListableProductsForBrand($productFilterData, $orderingModeId, $page, $limit, $brandId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param string|null $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     * @deprecated This method will be removed in next major version. Use \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterQueryFactory::createListableProductsForSearchText() instead.
     */
    protected function createListableProductsForSearchTextFilterQuery(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        ?string $searchText
    ): FilterQuery {
        return $this->filterQueryFactory->createListableProductsForSearchText($productFilterData, $orderingModeId, $page, $limit, $searchText);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery
     * @deprecated This method will be removed in next major version. Use \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterQueryFactory::createWithProductFilterData() instead.
     */
    protected function createFilterQueryWithProductFilterData(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit
    ): FilterQuery {
        return $this->filterQueryFactory->createWithProductFilterData($productFilterData, $orderingModeId, $page, $limit);
    }

    /**
     * @return string
     * @deprecated This method will be removed in next major version. Use \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterQueryFactory::getIndexName() instead.
     */
    protected function getIndexName(): string
    {
        return $this->filterQueryFactory->getIndexName();
    }
}
