<?php

declare(strict_types=1);

namespace App\Model\Product;

use App\Component\Domain\Domain;
use App\Model\CategorySeo\ReadyCategorySeoMix;
use App\Model\Product\Filter\ProductFilterCacheFacade;
use App\Model\Product\Search\FilterQueryFactory;
use App\Model\Product\Search\ProductElasticsearchRepository;
use App\Model\Product\Search\ProductFilterDataToQueryTransformer;
use Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\ProductOnCurrentDomainElasticFacade as BaseProductOnCurrentDomainElasticFacade;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

/**
 * @method \App\Model\Product\Product getVisibleProductById(int $productId)
 * @method \App\Model\Product\Product[] getAccessoriesForProduct(\App\Model\Product\Product $product)
 * @method \App\Model\Product\Product[] getVariantsForProduct(\App\Model\Product\Product $product)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsInCategory(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult getPaginatedProductsForSearch(string $searchText, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataInCategory(int $categoryId, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData getProductFilterCountDataForSearch(string|null $searchText, \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig, \App\Model\Product\Filter\ProductFilterData $productFilterData)
 * @method array getProductsByCategory(\App\Model\Category\Category $category, int $limit, int $offset, string $orderingModeId)
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Component\Domain\Domain $domain
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
 * @property \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @method \App\Model\Product\Search\FilterQuery createListableProductsInCategoryFilterQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $categoryId)
 * @method \App\Model\Product\Search\FilterQuery createListableProductsForBrandFilterQuery(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit, int $brandId)
 * @method \App\Model\Product\Search\FilterQuery createFilterQueryWithProductFilterData(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $orderingModeId, int $page, int $limit)
 */
class ProductOnCurrentDomainElasticFacade extends BaseProductOnCurrentDomainElasticFacade
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterCacheFacade
     */
    private $productFilterCacheFacade;

    /**
     * @param \App\Model\Product\ProductRepository $productRepository
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \App\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
     * @param \App\Model\Product\Search\ProductFilterDataToQueryTransformer $productFilterDataToQueryTransformer
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrameworkBundle\Component\Elasticsearch\IndexDefinitionLoader $indexDefinitionLoader
     * @param \App\Model\Product\Filter\ProductFilterCacheFacade $productFilterCacheFacade
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
        IndexDefinitionLoader $indexDefinitionLoader,
        ProductFilterCacheFacade $productFilterCacheFacade
    ) {
        parent::__construct(
            $productRepository,
            $domain,
            $currentCustomerUser,
            $productAccessoryRepository,
            $productElasticsearchRepository,
            $productFilterCountDataElasticsearchRepository,
            $productFilterDataToQueryTransformer,
            $filterQueryFactory,
            $indexDefinitionLoader
        );
        $this->productFilterCacheFacade = $productFilterCacheFacade;
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
        $filterQuery = $this->createListableProductsInCategoryFilterQuery($productFilterData, $orderingModeId, $page, $limit, $categoryId);
        /** @var \App\Model\Product\Search\FilterQuery $filterQuery */
        $filterQuery = $filterQuery->excludeProductByProductId($productId);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return new PaginationResult($page, $limit, $productsResult->getTotal(), $productsResult->getHits());
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getInSaleProductsHits(ProductFilterData $productFilterData): array
    {
        $baseFilterQuery = $this->filterQueryFactory->create($this->getIndexName())
            ->filterOnlyInSale()
            ->setLimit(15) // Temporary solution until SD-1543 will be done.
            ->filterOnlyVisible($this->currentCustomerUser->getPricingGroup())
            ->odrderByStockQuantity();
        $baseFilterQuery = $this->productFilterDataToQueryTransformer->addPricesToQuery($productFilterData, $baseFilterQuery, $this->currentCustomerUser->getPricingGroup());
        $baseFilterQuery = $this->productFilterDataToQueryTransformer->addStockToQuery($productFilterData, $baseFilterQuery);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($baseFilterQuery);

        return $productsResult->getHits();
    }

    /**
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
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

    /**
     * @param int $categoryId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig $productFilterConfig
     * @param \App\Model\Product\Filter\ProductFilterData $productFilterData
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getCachedProductFilterCountDataInCategory(
        int $categoryId,
        ProductFilterConfig $productFilterConfig,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix
    ): ?ProductFilterCountData {
        $isFilterActive = $productFilterData->isFilterActive($readyCategorySeoMix);
        if ($isFilterActive === false) {
            $productFilterCountData = $this->productFilterCacheFacade->findProductFilterCountDataInCache(
                $categoryId,
                $this->domain->getId(),
                $readyCategorySeoMix
            );
            if ($productFilterCountData !== null) {
                return $productFilterCountData;
            }
        }

        $productFilterCountData = $this->getProductFilterCountDataInCategory(
            $categoryId,
            $productFilterConfig,
            $productFilterData
        );

        if ($isFilterActive === false) {
            $this->productFilterCacheFacade->setProductFilterCountDataIntoCache(
                $productFilterCountData,
                $categoryId,
                $this->domain->getId(),
                $readyCategorySeoMix
            );
        }

        return $productFilterCountData;
    }
}
