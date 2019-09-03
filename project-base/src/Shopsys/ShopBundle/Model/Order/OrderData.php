<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;

/**
 * @property \Shopsys\ShopBundle\Model\Transport\Transport|null $transport
 * @property \Shopsys\ShopBundle\Model\Payment\Payment|null $payment
 * @property \Shopsys\ShopBundle\Model\Order\Item\OrderItemData[] $itemsWithoutTransportAndPayment
 * @property \Shopsys\ShopBundle\Model\Administrator\Administrator|null $createdAsAdministrator
 * @property \Shopsys\ShopBundle\Model\Order\Item\OrderItemData|null $orderPayment
 * @property \Shopsys\ShopBundle\Model\Order\Item\OrderItemData|null $orderTransport
 * @method \Shopsys\ShopBundle\Model\Order\Item\OrderItemData[] getNewItemsWithoutTransportAndPayment()
 */
class OrderData extends BaseOrderData
{
    public function __construct()
    {
        parent::__construct();
    }
}
