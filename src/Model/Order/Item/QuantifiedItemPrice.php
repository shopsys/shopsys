<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedItemPrice as BaseQuantifiedItemPrice;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class QuantifiedItemPrice extends BaseQuantifiedItemPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $unitHighPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitHighPrice
     */
    public function __construct(
        Price $unitPrice,
        Price $totalPrice,
        Vat $vat,
        Price $unitHighPrice
    ) {
        parent::__construct($unitPrice, $totalPrice, $vat);

        $this->unitHighPrice = $unitHighPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getUnitHighPrice(): Price
    {
        return $this->unitHighPrice;
    }
}
