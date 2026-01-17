<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Listing;

abstract class ProductListOrderingModeForListFacade
{
    public function getProductListOrderingConfig(): ProductListOrderingConfig
    {
        return new ProductListOrderingConfig(
            $this->getSupportedOrderingModesNamesById(),
            $this->getDefaultOrderingModeId(),
        );
    }

    /**
     * @return array<string, string>
     */
    abstract protected function getSupportedOrderingModesNamesById(): array;

    protected function getDefaultOrderingModeId(): string
    {
        return ProductListOrderingConfig::ORDER_BY_PRIORITY;
    }
}
