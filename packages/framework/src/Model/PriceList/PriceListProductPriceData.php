<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PriceListProductPriceData
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
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency|null
     */
    public $currency;

    public function getDiscount(): Money
    {
        return $this->basicPrice->subtract($this->priceAmount);
    }

    public function hasDiscount(): bool
    {
        return $this->basicPrice->isGreaterThan($this->priceAmount);
    }
}
