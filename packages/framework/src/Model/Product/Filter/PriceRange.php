<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceRange
{
    protected Money $minimalPrice;

    protected Money $maximalPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $minimalPrice
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $maximalPrice
     */
    public function __construct(Money $minimalPrice, Money $maximalPrice)
    {
        $this->minimalPrice = $minimalPrice;
        $this->maximalPrice = $maximalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getMinimalPrice()
    {
        return $this->minimalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getMaximalPrice()
    {
        return $this->maximalPrice;
    }
}
