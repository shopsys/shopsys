<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceRange
{
    public function __construct(protected readonly Money $minimalPrice, protected readonly Money $maximalPrice)
    {
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
