<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceRange
{
    public function __construct(protected readonly Money $minimalPrice, protected readonly Money $maximalPrice)
    {
    }

    public function getMinimalPrice(): Money
    {
        return $this->minimalPrice;
    }

    public function getMaximalPrice(): Money
    {
        return $this->maximalPrice;
    }
}
