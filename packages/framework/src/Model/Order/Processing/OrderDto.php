<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Processing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class OrderDto
{
    public Price $totalPrice;

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Model\Pricing\Price>
     */
    public array $totalPriceByItemType = [];

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData>
     */
    public array $quantifiedOrderItems = [];

    public array $quantifiedDiscounts = [];

    /**
     * @var array<int, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode>
     */
    public array $appliedPromoCodes = [];

    public array $additionalData = [];

    public function __construct()
    {
        $this->totalPrice = new Price(Money::zero(), Money::zero());

        $this->totalPriceByItemType[OrderItem::TYPE_PRODUCT] = new Price(Money::zero(), Money::zero());
        $this->totalPriceByItemType[OrderItem::TYPE_DISCOUNT] = new Price(Money::zero(), Money::zero());
        $this->totalPriceByItemType[OrderItem::TYPE_PAYMENT] = new Price(Money::zero(), Money::zero());
        $this->totalPriceByItemType[OrderItem::TYPE_TRANSPORT] = new Price(Money::zero(), Money::zero());
        $this->totalPriceByItemType[OrderItem::TYPE_ROUNDING] = new Price(Money::zero(), Money::zero());
    }
}
