<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Symfony\Component\HttpFoundation\Request;

abstract class ProductListOrderingModeForListFacade
{
    protected const string COOKIE_NAME = 'productListOrderingMode';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
     */
    public function __construct(
        protected readonly RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
     */
    public function getProductListOrderingConfig()
    {
        return new ProductListOrderingConfig(
            $this->getSupportedOrderingModesNamesById(),
            $this->getDefaultOrderingModeId(),
            static::COOKIE_NAME,
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix|null $readyCategorySeoMix
     * @return string
     */
    public function getOrderingModeIdFromRequest(
        Request $request,
        ?ReadyCategorySeoMix $readyCategorySeoMix = null,
    ) {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig(),
            $readyCategorySeoMix,
        );
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getSupportedOrderingModesNamesById(): array;

    /**
     * @return string
     */
    protected function getDefaultOrderingModeId(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
