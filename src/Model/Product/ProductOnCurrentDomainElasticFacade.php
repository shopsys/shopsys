<?php

declare(strict_types=1);

namespace App\Model\Product;

use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;

/**
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @method \App\Model\Product\Product getVisibleProductById(int $productId)
 * @method \App\Model\Product\Product[] getAccessoriesForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForProduct(\App\Model\Product\Product $product)
 * @method __construct(\App\Model\Product\ProductRepository $productRepository, \App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository, \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository, \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository, \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory, \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsInCategoryFilterQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsForBrandFilterQuery(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $brandId)
 * @method \App\Model\Product\Search\FilterQuery createFilterQueryWithProductFilterData(\Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method array getProductsByCategory(\App\Model\Category\Category $category, int $limit, int $offset, string $orderingModeId)
 * @property \App\Component\Domain\Domain $domain
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getInSaleProductsHits(ProductFilterData $productFilterData): array
    {
        $baseFilterQuery = $this->filterQueryFactory->create($this->getIndexName())
            ->filterOnlyInSale()
            ->filterOnlyVisible($this->currentCustomerUser->getPricingGroup())
            ->odrderByStockQuantity();
        $baseFilterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery($productFilterData, $baseFilterQuery, $this->currentCustomerUser->getPricingGroup());
        $baseFilterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $baseFilterQuery);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($baseFilterQuery);

        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @param string $orderingModeId
     * @param int $page
     * @param int $limit
     * @param string|null $searchText
     * @return \App\Model\Product\Search\FilterQuery
     */
    protected function createListableProductsForSearchTextFilterQuery(
        ProductFilterData $productFilterData,
        string $orderingModeId,
        int $page,
        int $limit,
        ?string $searchText
    ): FilterQuery {
        $searchText = $searchText ?? '';

        return $this->createFilterQueryWithProductFilterData($productFilterData, $orderingModeId, $page, $limit)
            ->search($searchText)
            ->filterNotExcludeOrInStock();
    }
}
