<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForBrandFacade as BaseProductListOrderingModeForBrandFacade;

/**
 * @property \App\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
 * @method __construct(\App\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
 */
class ProductListOrderingModeForBrandFacade extends BaseProductListOrderingModeForBrandFacade
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
     */
    public function getProductListOrderingConfig()
    {
        return new ProductListOrderingConfig(
            [
                ProductListOrderingConfig::ORDER_BY_PRIORITY => t('TOP'),
                ProductListOrderingConfig::ORDER_BY_PRICE_ASC => t('from the cheapest'),
                ProductListOrderingConfig::ORDER_BY_PRICE_DESC => t('from most expensive'),
            ],
            ProductListOrderingConfig::ORDER_BY_PRIORITY,
            static::COOKIE_NAME
        );
    }
}
