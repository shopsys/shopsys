<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Symfony\Component\HttpFoundation\Request;

abstract class ProductListOrderingModeForListFacade
{
    protected const COOKIE_NAME = 'productListOrderingMode';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
     */
    public function __construct(protected readonly RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
    {
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
     * @return string
     */
    public function getOrderingModeIdFromRequest(Request $request)
    {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig(),
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
