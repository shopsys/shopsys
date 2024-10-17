<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Symfony\Component\HttpFoundation\Request;

class RequestToOrderingModeIdConverter
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig $productListOrderingConfig
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    public function getOrderingModeIdFromRequest(
        Request $request,
        ProductListOrderingConfig $productListOrderingConfig,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ) {
        if ($readyCategorySeoMix !== null) {
            $readyCategorySeoMixOrderingModeId = $readyCategorySeoMix->getOrdering();

            if ($readyCategorySeoMixOrderingModeId !== null) {
                return $readyCategorySeoMixOrderingModeId;
            }
        }

        $orderingModeId = $request->cookies->get($productListOrderingConfig->getCookieName());

        if (!in_array($orderingModeId, $productListOrderingConfig->getSupportedOrderingModeIds(), true)) {
            $orderingModeId = $productListOrderingConfig->getDefaultOrderingModeId();
        }

        return $orderingModeId;
    }
}
