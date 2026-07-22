<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class OrderItemData
{
    /**
     * @var string|null
     */
    public $name;

    /**
     * If this attribute is set to true, all prices in this data object other that $priceWithVat will be ignored.
     * The prices will be recalculated when the OrderItem entity is edited.
     * This means you can set only a single price ($priceWithVat) and others will be calculated automatically.
     *
     * @var bool
     */
    public $usePriceCalculation = true;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $unitPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $unitPriceWithoutVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $totalPriceWithVat;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money|null
     */
    public $totalPriceWithoutVat;

    /**
     * @var string|null
     */
    public $vatPercent;

    /**
     * @var int|null
     */
    public $quantity;

    /**
     * @var string|null
     */
    public $unitName;

    /**
     * @var string|null
     */
    public $catnum;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport|null
     */
    public $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Payment\Payment|null
     */
    public $payment;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product|null
     */
    public $product;

    /**
     * @var \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService|null
     */
    public $additionalService;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null
     */
    public $promoCode;

    /**
     * @var string|null
     */
    public $type;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData[]
     */
    public $relatedOrderItemsData = [];

    public function setUnitPrice(PriceInterface $unitPrice): void
    {
        $this->unitPriceWithVat = $unitPrice->getPriceWithVat();
        $this->unitPriceWithoutVat = $unitPrice->getPriceWithoutVat();
    }

    public function setTotalPrice(PriceInterface $totalPrice): void
    {
        $this->totalPriceWithVat = $totalPrice->getPriceWithVat();
        $this->totalPriceWithoutVat = $totalPrice->getPriceWithoutVat();
    }

    public function getTotalPrice(): PriceInterface
    {
        return new Price($this->totalPriceWithoutVat, $this->totalPriceWithVat);
    }

    public function getUnitPrice(): PriceInterface
    {
        return new Price($this->unitPriceWithoutVat, $this->unitPriceWithVat);
    }
}
