<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceRange
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected $minimalPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected $maximalPrice;

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
    public function getMinimalPrice(): Money
    {
        return $this->minimalPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getMaximalPrice(): Money
    {
        return $this->maximalPrice;
    }
}
