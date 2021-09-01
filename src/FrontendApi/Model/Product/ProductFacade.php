<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Product;

use Shopsys\FrontendApiBundle\Model\Product\ProductFacade as BaseProductFacade;

/**
 * @method \App\Model\Product\Product getSellableByUuid(string $uuid, int $domainId, \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup)
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
