<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use Shopsys\FrontendApiBundle\Model\Product\ProductFacade as BaseProductFacade;

/**
 * @method \App\Model\Product\Product getSellableByUuid(string $uuid, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
 * @property \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory
 * @property \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository
 * @method int getFilteredProductsCountOnCurrentDomain(\App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsOnCurrentDomain(int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsByCategory(\App\Model\Category\Category $category, int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method int getFilteredProductsByCategoryCount(\App\Model\Category\Category $category, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method array getFilteredProductsByBrand(\App\Model\Product\Brand\Brand $brand, int $limit, int $offset, string $orderingModeId, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method int getFilteredProductsByBrandCount(\App\Model\Product\Brand\Brand $brand, \App\Model\Product\Filter\ProductFilterData $productFilterData, string $search)
 * @method __construct(\Shopsys\FrontendApiBundle\Model\Product\ProductRepository $productRepository, \App\Model\Product\Search\FilterQueryFactory $filterQueryFactory, \App\Model\Product\Search\ProductElasticsearchRepository $productElasticsearchRepository)
 */
class ProductFacade extends BaseProductFacade
{
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
}
