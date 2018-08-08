<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ProductSellingPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    private $pricingGroup;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $sellingPrice;

    public function __construct(PricingGroup $pricingGroup, Price $sellingPrice)
    {
        $this->pricingGroup = $pricingGroup;
        $this->sellingPrice = $sellingPrice;
    }

    public function getPricingGroup(): \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
    {
        return $this->pricingGroup;
    }

    public function getSellingPrice(): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return $this->sellingPrice;
    }
}
