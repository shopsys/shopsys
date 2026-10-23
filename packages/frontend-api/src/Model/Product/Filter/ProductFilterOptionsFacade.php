<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product\Filter;

use Closure;
use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Overblog\DataLoader\DataLoaderInterface;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Module\ModuleFacade;
use Shopsys\FrameworkBundle\Model\Module\ModuleList;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQuery;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

class ProductFilterOptionsFacade
{
    public function __construct(
        protected readonly ModuleFacade $moduleFacade,
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly DataLoaderInterface $elasticsearchSearchBatchLoader,
        protected readonly ProductFilterFacade $productFilterFacade,
        protected readonly ProductFilterOptionsFactory $productFilterOptionsFactory,
        protected readonly FilterQueryFactory $filterQueryFactory,
        protected readonly ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
    ) {
    }

    public function getProductFilterOptionsPromiseForAll(ProductFilterData $productFilterData): Promise
    {
        return $this->getProductFilterOptionsPromise(
            fn (): Promise => $this->productFilterFacade->getProductFilterConfigPromiseForAll(),
            fn (): Promise => $this->getProductFilterCountDataPromise(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsWithPriceAndStockFilter($productFilterData),
                false,
            ),
            fn (ProductFilterConfig $productFilterConfig, ProductFilterCountData $productFilterCountData): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsForAll(
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
            ),
        );
    }

    public function getProductFilterOptionsPromiseForSearch(
        ProductFilterData $productFilterData,
        string $searchText,
    ): Promise {
        return $this->getProductFilterOptionsPromise(
            fn (): Promise => $this->productFilterFacade->getProductFilterConfigPromiseForSearch($searchText),
            fn (): Promise => $this->getProductFilterCountDataPromise(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsBySearchTextWithPriceAndStockFilter(
                    $searchText,
                    $productFilterData,
                ),
                false,
            ),
            fn (ProductFilterConfig $productFilterConfig, ProductFilterCountData $productFilterCountData): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsForAll(
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
            ),
        );
    }

    public function getProductFilterOptionsPromiseForCategory(
        Category $category,
        ProductFilterData $productFilterData,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): Promise {
        return $this->getProductFilterOptionsPromise(
            fn (): Promise => $this->productFilterFacade->getProductFilterConfigPromiseForCategory($category),
            fn (): Promise => $this->getProductFilterCountDataPromise(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByCategoryWithPriceAndStockFilter(
                    $category,
                    $productFilterData,
                ),
                true,
            ),
            fn (ProductFilterConfig $productFilterConfig, ProductFilterCountData $productFilterCountData): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsForCategory(
                $category,
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
                $readyCategorySeoMix,
            ),
        );
    }

    public function getProductFilterOptionsPromiseForBrand(Brand $brand, ProductFilterData $productFilterData): Promise
    {
        return $this->getProductFilterOptionsPromise(
            fn (): Promise => $this->productFilterFacade->getProductFilterConfigPromiseForBrand($brand),
            fn (): Promise => $this->getProductFilterCountDataPromise(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByBrandIdWithPriceAndStockFilter(
                    $brand->getId(),
                    $productFilterData,
                ),
                true,
            ),
            fn (ProductFilterConfig $productFilterConfig, ProductFilterCountData $productFilterCountData): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsForBrand(
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
            ),
        );
    }

    public function getProductFilterOptionsPromiseForFlag(Flag $flag, ProductFilterData $productFilterData): Promise
    {
        return $this->getProductFilterOptionsPromise(
            fn (): Promise => $this->productFilterFacade->getProductFilterConfigPromiseForFlag($flag),
            fn (): Promise => $this->getProductFilterCountDataPromise(
                $productFilterData,
                $this->filterQueryFactory->createListableProductsByFlagIdWithPriceAndStockFilter(
                    $flag->getId(),
                    $productFilterData,
                ),
                true,
            ),
            fn (ProductFilterConfig $productFilterConfig, ProductFilterCountData $productFilterCountData): ProductFilterOptions => $this->productFilterOptionsFactory->createProductFilterOptionsForFlag(
                $productFilterConfig,
                $productFilterCountData,
                $productFilterData,
            ),
        );
    }

    protected function getProductFilterOptionsPromise(
        Closure $getProductFilterConfigPromise,
        Closure $getProductFilterCountDataPromise,
        Closure $createProductFilterOptions,
    ): Promise {
        if (!$this->moduleFacade->isEnabled(ModuleList::PRODUCT_FILTER_COUNTS)) {
            return $this->promiseAdapter->createFulfilled($this->productFilterOptionsFactory->createEmptyProductFilterOptions());
        }

        return $this->promiseAdapter->all([
            $getProductFilterConfigPromise(),
            $getProductFilterCountDataPromise(),
        ])->then(
            static fn (array $productFilterConfigAndCountData): ProductFilterOptions => $createProductFilterOptions(...$productFilterConfigAndCountData),
        );
    }

    protected function getProductFilterCountDataPromise(
        ProductFilterData $productFilterData,
        FilterQuery $baseFilterQuery,
        bool $withParameters,
    ): Promise {
        $searchQueriesIndexedByQueryName = $this->productFilterCountDataElasticsearchRepository->createSearchQueriesIndexedByQueryName(
            $productFilterData,
            $baseFilterQuery,
            $withParameters,
        );

        return $this->elasticsearchSearchBatchLoader->loadMany($searchQueriesIndexedByQueryName)->then(
            fn (array $responsesIndexedByQueryName): ProductFilterCountData => $this->productFilterCountDataElasticsearchRepository->createCountData(
                $productFilterData,
                $responsesIndexedByQueryName,
                $withParameters,
            ),
        );
    }
}
