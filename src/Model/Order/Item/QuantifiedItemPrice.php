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
    protected $totalHighPrice;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $unitPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalPrice
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat $vat
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $totalHighPrice
     */
    public function __construct(
        Price $unitPrice,
        Price $totalPrice,
        Vat $vat,
        Price $totalHighPrice
    ) {
        parent::__construct($unitPrice, $totalPrice, $vat);

        $this->totalHighPrice = $totalHighPrice;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    public function getTotalHighPrice(): Price
    {
        return $this->totalHighPrice;
    }
}
