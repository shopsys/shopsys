<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;

class QuantifiedItemPrice
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $unitPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Price
     */
    private $totalPrice;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    private $vat;

    public function __construct(
        Price $unitPrice,
        Price $totalPrice,
        Vat $vat
    ) {
        $this->unitPrice = $unitPrice;
        $this->totalPrice = $totalPrice;
        $this->vat = $vat;
    }

    public function getUnitPrice(): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return $this->unitPrice;
    }

    public function getTotalPrice(): \Shopsys\FrameworkBundle\Model\Pricing\Price
    {
        return $this->totalPrice;
    }

    public function getVat(): \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
    {
        return $this->vat;
    }
}
