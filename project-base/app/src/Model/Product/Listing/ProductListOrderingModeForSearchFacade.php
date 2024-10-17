<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForSearchFacade as BaseProductListOrderingModeForSearchFacade;

/**
 * @property \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
 * @method __construct(\Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
 */
class ProductListOrderingModeForSearchFacade extends BaseProductListOrderingModeForSearchFacade
{
    /**
     * @return array<string, string>
     */
    protected function getSupportedOrderingModesNamesById(): array
    {
        return [
            ProductListOrderingConfig::ORDER_BY_RELEVANCE => t('relevance'),
            ProductListOrderingConfig::ORDER_BY_PRIORITY => t('TOP'),
            ProductListOrderingConfig::ORDER_BY_PRICE_ASC => t('from the cheapest'),
            ProductListOrderingConfig::ORDER_BY_PRICE_DESC => t('from most expensive'),
        ];
    }
}
