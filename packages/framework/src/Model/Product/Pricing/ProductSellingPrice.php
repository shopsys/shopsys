<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ProductSellingPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    protected $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    protected $sellingPrice;

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
