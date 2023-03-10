<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Model\Product\Filter\ProductFilterDataFactory;
use App\Model\Product\Search\FilterQueryFactory;
use App\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

/**
 * @method \App\Model\Product\Product getVisibleProductById(int $productId)
 * @method \App\Model\Product\Product[] getAccessoriesForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsForSearch(string $searchText, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForSearch(string|null $searchText, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForAll(\App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsInCategory(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class ProductOnCurrentDomainElasticFacade extends BaseProductOnCurrentDomainElasticFacade
{
    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        ProductAccessoryRepository $productAccessoryRepository,
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        FilterQueryFactory $filterQueryFactory,
        private readonly ProductFilterDataFactory $productFilterDataFactory
    ) {
        parent::__construct(
            $productRepository,
            $domain,
            $currentCustomerUser,
            $productAccessoryRepository,
            $productElasticsearchRepository,
            $productFilterCountDataElasticsearchRepository,
            $filterQueryFactory
        );
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
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
        $filterQuery = $this->filterQueryFactory->createListableProductsByCategoryId($productFilterData, $orderingModeId, $page, $limit, $categoryId);
        /** @var \App\Model\Product\Search\FilterQuery $filterQuery */
        $filterQuery = $filterQuery->excludeProductByProductId($productId);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * @param int $flagId
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForFlag(
        int $flagId,
        ProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterCountData {
        $filterQuery = $this->filterQueryFactory->createListableProductsByFlagIdWithPriceAndStockFilter(
            $flagId,
            $productFilterData
        );
        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $filterQuery
        );
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param int $brandId
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getPaginatedProductsForBrand(
        string $orderingModeId,
        int $page,
        int $limit,
        int $brandId
    ): PaginationResult {
        $emptyProductFilterData = $this->productFilterDataFactory->create();

        $filterQuery = $this->filterQueryFactory->createListableProductsByBrandId(
            $emptyProductFilterData,
            $orderingModeId,
            $page,
            $limit,
            $brandId
        );

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param string|null $searchText
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getSearchAutocompleteProducts(?string $searchText, int $limit): PaginationResult
    {
        $searchText = $searchText ?? '';

        $emptyProductFilterData = $this->productFilterDataFactory->create();
        $page = 1;

        $filterQuery = $this->filterQueryFactory->createListableProductsBySearchText(
            $emptyProductFilterData,
            ProductListOrderingConfig::ORDER_BY_RELEVANCE,
            $page,
            $limit,
            $searchText
        );

        $productIds = $this->productElasticsearchRepository->getSortedProductIdsByFilterQuery($filterQuery);

        $listableProductsByIds = $this->productRepository->getListableByIds(
            $this->domain->getId(),
            $this->currentCustomerUser->getPricingGroup(),
            $productIds->getIds()
        );

        return new PaginationResult($page, $limit, $productIds->getTotal(), $listableProductsByIds);
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData)
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataInCategory(
        int $categoryId,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterCountData {
        $baseFilterQuery = $this->filterQueryFactory->createListableProductsByCategoryIdWithPriceAndStockFilter(
            $categoryId,
            $productFilterData
        );
        if ($searchText !== '') {
            $baseFilterQuery = $baseFilterQuery->search($searchText);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $baseFilterQuery
        );
    }

    /**
     * @param int $brandId
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $searchText
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForBrand(
        int $brandId,
        ProductFilterData $productFilterData,
        string $searchText = ''
    ): ProductFilterCountData {
        $filterQuery = $this->filterQueryFactory->createListableProductsByBrandIdWithPriceAndStockFilter(
            $brandId,
            $productFilterData
        );
        if ($searchText !== '') {
            $filterQuery = $filterQuery->search($searchText);
        }

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $filterQuery
        );
    }
}
