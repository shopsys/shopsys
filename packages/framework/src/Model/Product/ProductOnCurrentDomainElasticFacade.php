<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

class ProductOnCurrentDomainElasticFacade
{
    public function __construct(
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    public function getProductFilterCountDataInCategory(
        Category $category,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        $baseFilterQuery = $this->filterQueryFactory->createListableProductsByCategoryWithPriceAndStockFilter(
            $category,
            $productFilterData,
        );

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $baseFilterQuery,
        );
    }

    public function getProductFilterCountDataForBrand(
        int $brandId,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $this->filterQueryFactory->createListableProductsByBrandIdWithPriceAndStockFilter(
                $brandId,
                $productFilterData,
            ),
        );
    }

    public function getProductFilterCountDataForSearch(
        ?string $searchText,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        $searchText ??= '';

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInSearch(
            $productFilterData,
            $this->filterQueryFactory->createListableProductsBySearchTextWithPriceAndStockFilter(
                $searchText,
                $productFilterData,
            ),
        );
    }

    public function getProductFilterCountDataForAll(
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInSearch(
            $productFilterData,
            $this->filterQueryFactory->createListableProductsWithPriceAndStockFilter($productFilterData),
        );
    }

    public function getProductFilterCountDataForFlag(
        int $flagId,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        $filterQuery = $this->filterQueryFactory->createListableProductsByFlagIdWithPriceAndStockFilter(
            $flagId,
            $productFilterData,
        );

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInCategory(
            $productFilterData,
            $filterQuery,
        );
    }

    /**
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData): array
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }
}
