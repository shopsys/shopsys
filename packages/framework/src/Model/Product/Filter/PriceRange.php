<?php

namespace Shopsys\FrameworkBundle\Model\Product\Filter;

class PriceRange
{
    /**
     * @var string
     */
    private $minimalPrice;

    /**
     * @var string
     */
    private $maximalPrice;

    public function __construct(?string $minimalPrice, ?string $maximalPrice)
    {
        $this->minimalPrice = $minimalPrice === null ? '0' : $minimalPrice;
        $this->maximalPrice = $maximalPrice === null ? '0' : $maximalPrice;
    }

    public function getMinimalPrice(): string
    {
        return $this->minimalPrice;
    }

    public function getMaximalPrice(): string
    {
        return $this->maximalPrice;
    }
}
