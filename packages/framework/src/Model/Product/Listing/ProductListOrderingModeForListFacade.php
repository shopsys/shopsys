<?php

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

use Symfony\Component\HttpFoundation\Request;

/**
 * @deprecated Class will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.
 */
class ProductListOrderingModeForListFacade
{
    protected const COOKIE_NAME = 'productListOrderingMode';

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter
     */
    protected $requestToOrderingModeIdConverter;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Listing\RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter
     */
    public function __construct(RequestToOrderingModeIdConverter $requestToOrderingModeIdConverter)
    {
        if (static::class === self::class) {
            trigger_error(
                sprintf(
                    'Class "%s" will be changed to abstract class in next major version. Extend this class to your project and implement corresponding methods instead.',
                    self::class
                ),
                E_USER_DEPRECATED
            );
        }

        $this->requestToOrderingModeIdConverter = $requestToOrderingModeIdConverter;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Listing\ProductListOrderingConfig
     */
    public function getProductListOrderingConfig()
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
    public function getOrderingModeIdFromRequest(Request $request)
    {
        return $this->requestToOrderingModeIdConverter->getOrderingModeIdFromRequest(
            $request,
            $this->getProductListOrderingConfig()
        );
    }

    /**
     * @deprecated Method will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.
     * @return array<string, string>
     */
    protected function getSupportedOrderingModesNamesById(): array
    {
        trigger_error(
            sprintf(
                'Method "%s" will be changed to abstract in next major version. Extend this class to your project and implement method by yourself instead.',
                __METHOD__
            ),
            E_USER_DEPRECATED
        );

        return [
            ProductListOrderingConfig::ORDER_BY_PRIORITY => t('TOP'),
            ProductListOrderingConfig::ORDER_BY_NAME_ASC => t('alphabetically A -> Z'),
            ProductListOrderingConfig::ORDER_BY_NAME_DESC => t('alphabetically Z -> A'),
            ProductListOrderingConfig::ORDER_BY_PRICE_ASC => t('from the cheapest'),
            ProductListOrderingConfig::ORDER_BY_PRICE_DESC => t('from most expensive'),
        ];
    }

    /**
     * @return string
     */
    protected function getDefaultOrderingModeId(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
