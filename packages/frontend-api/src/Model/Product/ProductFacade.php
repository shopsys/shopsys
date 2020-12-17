<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;

class ProductFacade
{
    /**
     * @var \Shopsys\FrontendApiBundle\Model\Product\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory
     */
    protected $filterQueryFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository
     */
    protected $productElasticsearchRepository;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductRepository $productRepository
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     */
    public function __construct(
        ProductRepository $productRepository,
        FilterQueryFactory $filterQueryFactory,
        ProductElasticsearchRepository $productElasticsearchRepository
    ) {
        $this->productRepository = $productRepository;
        $this->filterQueryFactory = $filterQueryFactory;
        $this->productElasticsearchRepository = $productElasticsearchRepository;
    }

    /**
     * @param string $uuid
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getSellableByUuid(string $uuid, int $domainId, PricingGroup $pricingGroup): Product
    {
        return $this->productRepository->getSellableByUuid($uuid, $domainId, $pricingGroup);
    }

    /**
     * @return int
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsCountOnCurrentDomain()
     */
    public function getProductsCountOnCurrentDomain(): int
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsCountOnCurrentDomain().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $filterQuery = $this->filterQueryFactory->createListable();

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int
     */
    public function getFilteredProductsCountOnCurrentDomain(ProductFilterData $productFilterData): int
    {
        $filterQuery = $this->filterQueryFactory->createListableWithProductFilter($productFilterData);

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsOnCurrentDomain()
     */
    public function getProductsOnCurrentDomain(int $limit, int $offset, string $orderingModeId): array
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsOnCurrentDomain().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

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
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getFilteredProductsOnCurrentDomain(
        int $limit,
        int $offset,
        string $orderingModeId,
        ProductFilterData $productFilterData
    ): array {
        $filterQuery = $this->filterQueryFactory->createWithProductFilterData(
            $productFilterData,
            $orderingModeId,
            1,
            $limit
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByCategory()
     */
    public function getProductsByCategory(Category $category, int $limit, int $offset, string $orderingModeId): array
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsByCategory().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $emptyProductFilterData = new ProductFilterData();
        $filterQuery = $this->filterQueryFactory->createListableProductsByCategoryId(
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
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getFilteredProductsByCategory(
        Category $category,
        int $limit,
        int $offset,
        string $orderingModeId,
        ProductFilterData $productFilterData
    ): array {
        $filterQuery = $this->filterQueryFactory->createListableProductsByCategoryId(
            $productFilterData,
            $orderingModeId,
            1,
            $limit,
            $category->getId()
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @return int
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByCategoryCount()
     */
    public function getProductsByCategoryCount(Category $category): int
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsByCategoryCount().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $filterQuery = $this->filterQueryFactory->createListable()
            ->filterByCategory([$category->getId()]);

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\Category $category
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int
     */
    public function getFilteredProductsByCategoryCount(Category $category, ProductFilterData $productFilterData): int
    {
        $filterQuery = $this->filterQueryFactory->createListableWithProductFilter($productFilterData)
            ->filterByCategory([$category->getId()]);

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByBrand()
     */
    public function getProductsByBrand(Brand $brand, int $limit, int $offset, string $orderingModeId): array
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsByBrand().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $emptyProductFilterData = new ProductFilterData();
        $filterQuery = $this->filterQueryFactory->createListableProductsByBrandId(
            $emptyProductFilterData,
            $orderingModeId,
            1,
            $limit,
            $brand->getId()
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);
        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return array
     */
    public function getFilteredProductsByBrand(
        Brand $brand,
        int $limit,
        int $offset,
        string $orderingModeId,
        ProductFilterData $productFilterData
    ): array {
        $filterQuery = $this->filterQueryFactory->createListableProductsByBrandId(
            $productFilterData,
            $orderingModeId,
            1,
            $limit,
            $brand->getId()
        )->setFrom($offset);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);
        return $productsResult->getHits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @return int
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByBrandCount()
     */
    public function getProductsByBrandCount(Brand $brand): int
    {
        @trigger_error(
            sprintf(
                'The %s() method is deprecated and will be removed in the next major. It has been replaced by getFilteredProductsByBrandCount().',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        $filterQuery = $this->filterQueryFactory->createListable()
            ->filterByBrands([$brand->getId()]);

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand $brand
     * @param \Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData $productFilterData
     * @return int
     */
    public function getFilteredProductsByBrandCount(Brand $brand, ProductFilterData $productFilterData): int
    {
        $filterQuery = $this->filterQueryFactory->createListableWithProductFilter($productFilterData)
            ->filterByBrands([$brand->getId()]);

        return $this->productElasticsearchRepository->getProductsCountByFilterQuery($filterQuery);
    }
}
