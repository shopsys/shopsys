<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 */
class OrderItemData extends BaseOrderItemData
{
    /**
     * @var \App\Model\Product\Type\ProductType|null
     */
    public $productType;

    /**
     * @var \App\Model\Stock\Stock|null
     */
    public $personalPickupStock;

    /**
     * @var string|null
     */
    public $promoCodeIdentifier;

    /**
     * @var \App\Model\Order\Item\OrderItem|null
     */
    public $relatedOrderItem;
}
