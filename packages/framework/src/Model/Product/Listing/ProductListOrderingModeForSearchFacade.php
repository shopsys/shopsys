<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Symfony\Component\HttpFoundation\Request;

abstract class ProductListOrderingModeForSearchFacade
{
    protected const COOKIE_NAME = 'productSearchOrderingMode';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter
     */
    protected $requestToOrderingModeIdConverter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
     */
    public function __construct(RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
    {
        $this->requestToOrderingModeIdConverter = $requestToOrderingModeIdConverter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
     */
    public function getProductListOrderingConfig(): \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
    {
        return new ProductListOrderingConfig(
            $this->getSupportedOrderingModesNamesById(),
            $this->getDefaultOrderingModeId(),
            static::COOKIE_NAME
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return string
     */
    public function getOrderingModeIdFromRequest(Request $request): string
    {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig()
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
        return ProductListOrderingConfig::ORDER_BY_RELEVANCE;
    }
}
