<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData as BaseOrderItemData;

/**
 * @property \Shopsys\ShopBundle\Model\Transport\Transport|null $transport
 * @property \Shopsys\ShopBundle\Model\Payment\Payment|null $payment
 */
class OrderItemData extends BaseOrderItemData
{
}
