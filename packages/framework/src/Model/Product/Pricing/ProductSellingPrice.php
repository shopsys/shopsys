<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;

final class ProductSellingPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $productPrice
     */
    public function __construct(
        private readonly PricingGroup $pricingGroup,
        private readonly ProductPrice $productPrice,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): PricingGroup
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function getProductPrice(): ProductPrice
    {
        return $this->productPrice;
    }
}
