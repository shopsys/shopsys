<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade as BaseProductListOrderingModeForBrandFacade;

class ProductListOrderingModeForBrandFacade extends BaseProductListOrderingModeForBrandFacade
{
    /**
     * @return array<string, string>
     */
    protected function getSupportedOrderingModesNamesById(): array
    {
        return [
            ProductListOrderingConfig::ORDER_BY_PRIORITY => t('TOP'),
            ProductListOrderingConfig::ORDER_BY_NAME_ASC => t('alphabetically A -> Z'),
            ProductListOrderingConfig::ORDER_BY_NAME_DESC => t('alphabetically Z -> A'),
            ProductListOrderingConfig::ORDER_BY_PRICE_ASC => t('from the cheapest'),
            ProductListOrderingConfig::ORDER_BY_PRICE_DESC => t('from most expensive'),
        ];
    }
}
