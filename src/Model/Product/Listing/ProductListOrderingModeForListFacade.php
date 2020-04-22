<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

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
        return parent::getProductListOrderingConfig();
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int|null $readyCategorySeoMixId
     * @return string
     */
    public function getOrderingModeIdFromRequest(Request $request, ?int $readyCategorySeoMixId = null)
    {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig(),
            $readyCategorySeoMixId
        );
    }
}
