<?php

declare(strict_types=1);

namespace App\Model\Product\Listing;

use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig;
use Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter as BaseRequestToOrderingModeIdConverter;
use Symfony\Component\HttpFoundation\Request;

class RequestToOrderingModeIdConverter extends BaseRequestToOrderingModeIdConverter
{
    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private $readyCategorySeoMixFacade;

    /**
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(ReadyCategorySeoMixFacade $readyCategorySeoMixFacade)
    {
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig $productListOrderingConfig
     * @param int|null $readyCategorySeoMixId
     * @return string
     */
    public function getOrderingModeIdFromRequest(
        Request $request,
        ProductListOrderingConfig $productListOrderingConfig,
        ?int $readyCategorySeoMixId = null
    ) {
        $forceOrderingModeId = $this->getForceOrderingModeId($request, $productListOrderingConfig);
        if ($forceOrderingModeId !== null) {
            return $forceOrderingModeId;
        }

        if ($readyCategorySeoMixId !== null) {
            $readyCategorySeoMixOrderingModeId = $this->getReadyCategorySeoMixOrderingModeId($readyCategorySeoMixId);
            if ($readyCategorySeoMixOrderingModeId !== null) {
                return $readyCategorySeoMixOrderingModeId;
            }
        }

        return parent::getOrderingModeIdFromRequest($request, $productListOrderingConfig);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig $productListOrderingConfig
     * @return string|null
     */
    private function getForceOrderingModeId(
        Request $request,
        ProductListOrderingConfig $productListOrderingConfig
    ): ?string {
        return $request->cookies->get('force-' . $productListOrderingConfig->getCookieName());
    }

    /**
     * @param int $readyCategorySeoMixId
     * @return string|null
     */
    private function getReadyCategorySeoMixOrderingModeId(int $readyCategorySeoMixId): ?string
    {
        $readyCategorySeoMix = $this->readyCategorySeoMixFacade->findById($readyCategorySeoMixId);
        return $readyCategorySeoMix->getOrdering();
    }
}
