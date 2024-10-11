<?php

declare(strict_types=1);

namespace App\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Product\Product|null $product
 * @property \App\Model\Order\Item\OrderItemData[] $relatedOrderItemsData
 */
class OrderItemData extends BaseOrderItemData
{
}
