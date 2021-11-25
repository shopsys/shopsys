<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use App\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Component\Deprecations\DeprecationHelper;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Search\FilterQueryFactory;
use Shopsys\FrameworkBundle\Model\Product\Search\ProductElasticsearchRepository;
use Shopsys\FrontendApiBundle\Model\Product\ProductFacade as BaseProductFacade;
use Shopsys\FrontendApiBundle\Model\Product\ProductRepository;

/**
 * @method \App\Model\Product\Product getSellableByUuid(string $uuid, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @method int getFilteredProductsCountOnCurrentDomain(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsOnCurrentDomain(int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsByCategory(\App\Model\Category\Category $category, int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method int getProductsByCategoryCount(\App\Model\Category\Category $category)
 * @method int getFilteredProductsByCategoryCount(\App\Model\Category\Category $category, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsByBrand(\App\Model\Product\Brand\Brand $brand, int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method int getProductsByBrandCount(\App\Model\Product\Brand\Brand $brand)
 * @method int getFilteredProductsByBrandCount(\App\Model\Product\Brand\Brand $brand, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 */
class ProductFacade extends BaseProductFacade
{
    /**
     * @var \App\Model\Product\Filter\ProductFilterDataFactory
     */
    private ProductFilterDataFactory $productFilterDataFactory;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Product\ProductRepository $productRepository
     * @param \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
     * @param \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
     * @param \App\Model\Product\Filter\ProductFilterDataFactory $productFilterDataFactory
     */
    public function __construct(
        ProductRepository $productRepository,
        FilterQueryFactory $filterQueryFactory,
        ProductElasticsearchRepository $productElasticsearchRepository,
        ProductFilterDataFactory $productFilterDataFactory
    ) {
        parent::__construct($productRepository, $filterQueryFactory, $productElasticsearchRepository);

        $this->productFilterDataFactory = $productFilterDataFactory;
    }

    /**
     * @param array $productIds
     * @return array
     */
    public function getSellableProductsByIds(array $productIds): array
    {
        $filterQuery = $this->filterQueryFactory->createSellableProductsByProductIdsFilter($productIds);

        $productsResult = $this->productElasticsearchRepository->getSortedProductsResultByFilterQuery($filterQuery);

        return $productsResult->getHits();
    }

    /**
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsOnCurrentDomain()
     */
    public function getProductsOnCurrentDomain(int $limit, int $offset, string $orderingModeId): array
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'getFilteredProductsOnCurrentDomain');

        $emptyProductFilterData = $this->productFilterDataFactory->create();
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
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param \App\Model\Category\Category $category
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByCategory()
     */
    public function getProductsByCategory(Category $category, int $limit, int $offset, string $orderingModeId): array
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'getFilteredProductsByCategory');

        $emptyProductFilterData = $this->productFilterDataFactory->create();
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
     * Method is extended because of https://github.com/shopsys/shopsys/pull/2380
     *
     * @param \App\Model\Product\Brand\Brand $brand
     * @param int $limit
     * @param int $offset
     * @param string $orderingModeId
     * @return array
     * @deprecated This method will be removed in next major release. It has been replaced with getFilteredProductsByBrand()
     */
    public function getProductsByBrand(Brand $brand, int $limit, int $offset, string $orderingModeId): array
    {
        DeprecationHelper::triggerMethod(__METHOD__, 'getFilteredProductsByBrand');

        $emptyProductFilterData = $this->productFilterDataFactory->create();
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
}
