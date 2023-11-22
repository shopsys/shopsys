<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ProductSellingPrice
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $sellingPrice
     */
    public function __construct(protected readonly PricingGroup $pricingGroup, protected readonly Price $sellingPrice)
    {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getSellingPrice(): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return $this->sellingPrice;
    }
}
