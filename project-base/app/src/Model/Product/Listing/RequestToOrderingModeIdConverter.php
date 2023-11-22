<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use App\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter as BaseRequestToOrderingModeIdConverter;
use Symfony\Component\HttpFoundation\Request;

class RequestToOrderingModeIdConverter extends BaseRequestToOrderingModeIdConverter
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig $productListOrderingConfig
     * @param \App\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    public function getOrderingModeIdFromRequest(
        Request $request,
        ProductListOrderingConfig $productListOrderingConfig,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ): string {
        if ($readyCategorySeoMix !== null) {
            $readyCategorySeoMixOrderingModeId = $readyCategorySeoMix->getOrdering();

            if ($readyCategorySeoMixOrderingModeId !== null) {
                return $readyCategorySeoMixOrderingModeId;
            }
        }

        return parent::getOrderingModeIdFromRequest($request, $productListOrderingConfig);
    }
}
