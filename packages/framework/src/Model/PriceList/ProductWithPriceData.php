<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Money\Money;

class ProductWithPriceData
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $priceAmount;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $product;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $basicPrice;

    /**
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public function getDiscount(): Money
    {
        return $this->basicPrice->subtract($this->priceAmount);
    }

    /**
     * @return bool
     */
    public function hasDiscount(): bool
    {
        return $this->basicPrice->isGreaterThan($this->priceAmount);
    }
}
