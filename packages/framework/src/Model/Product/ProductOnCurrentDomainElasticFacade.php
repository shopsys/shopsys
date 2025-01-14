<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository;

class ProductOnCurrentDomainElasticFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Accessory\ProductAccessoryRepository $productAccessoryRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     */
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductAccessoryRepository $productAccessoryRepository,
        protected readonly ProductElasticsearchRepository $productElasticsearchRepository,
        protected readonly ProductFilterCountDataElasticsearchRepository $productFilterCountDataElasticsearchRepository,
        protected readonly FilterQueryFactory $filterQueryFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
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

    /**
     * @param int $brandId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
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

    /**
     * @param string|null $searchText
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForSearch(
        ?string $searchText,
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        $searchText = $searchText ?? '';

        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInSearch(
            $productFilterData,
            $this->filterQueryFactory->createListableProductsBySearchTextWithPriceAndStockFilter(
                $searchText,
                $productFilterData,
            ),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
    public function getProductFilterCountDataForAll(
        ProductFilterData $productFilterData,
    ): ProductFilterCountData {
        return $this->productFilterCountDataElasticsearchRepository->getProductFilterCountDataInSearch(
            $productFilterData,
            $this->filterQueryFactory->createListableProductsWithPriceAndStockFilter($productFilterData),
        );
    }

    /**
     * @param int $flagId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterCountData
     */
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int[]
     */
    public function getCategoryIdsForFilterData(ProductFilterData $productFilterData): array
    {
        return $this->productElasticsearchRepository->getCategoryIdsForFilterData($productFilterData);
    }
}
