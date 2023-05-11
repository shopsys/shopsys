<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ProductSellingPrice
{
    protected PricingGroup $pricingGroup;

    protected Price $sellingPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $sellingPrice
     */
    public function __construct(PricingGroup $pricingGroup, Price $sellingPrice)
    {
        $this->pricingGroup = $pricingGroup;
        $this->sellingPrice = $sellingPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup()
    {
        return $this->pricingGroup;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getSellingPrice()
    {
        return $this->sellingPrice;
    }
}
