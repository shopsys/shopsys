<?php

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Price;

class ProductPrice extends Price
{
    /**
     * @var bool
     */
    private $priceFrom;

    public function __construct(Price $price, bool $priceFrom)
    {
        $this->priceFrom = $priceFrom;
        parent::__construct($price->getPriceWithoutVat(), $price->getPriceWithVat());
    }

    public function isPriceFrom(): bool
    {
        return $this->priceFrom;
    }
}
