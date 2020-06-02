<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingModeForListFacade as BaseProductListOrderingModeForListFacade;
use Symfony\Component\HttpFoundation\Request;

/**
 * @property \App\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
 * @method __construct(\App\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
 */
class ProductListOrderingModeForListFacade extends BaseProductListOrderingModeForListFacade
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
     */
    public function getProductListOrderingConfig()
    {
        // Removing of ordering mode needs remove App\Model\CategorySeo\ReadyCategorySeoMix that use it
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

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    public function getOrderingModeIdFromRequest(Request $request, ?ReadyCategorySeoMix $readyCategorySeoMix = null)
    {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig(),
            $readyCategorySeoMix
        );
    }
}
