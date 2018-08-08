<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Symfony\Component\HttpFoundation\Request;

class ProductListOrderingModeService
{
    const ORDER_BY_RELEVANCE = 'relevance';
    const ORDER_BY_NAME_ASC = 'name_asc';
    const ORDER_BY_NAME_DESC = 'name_desc';
    const ORDER_BY_PRICE_ASC = 'price_asc';
    const ORDER_BY_PRICE_DESC = 'price_desc';
    const ORDER_BY_PRIORITY = 'priority';

    public function getOrderingModeIdFromRequest(
        Request $request,
        ProductListOrderingConfig $productListOrderingConfig
    ): string {
        $orderingModeId = $request->cookies->get($productListOrderingConfig->getCookieName());

        if (!in_array($orderingModeId, $this->getSupportedOrderingModeIds($productListOrderingConfig), true)) {
            $orderingModeId = $productListOrderingConfig->getDefaultOrderingModeId();
        }

        return $orderingModeId;
    }

    /**
     * @return string[]
     */
    private function getSupportedOrderingModeIds(ProductListOrderingConfig $productListOrderingConfig): array
    {
        return array_keys($productListOrderingConfig->getSupportedOrderingModesNamesIndexedById());
    }
}
