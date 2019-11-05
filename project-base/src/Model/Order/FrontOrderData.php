<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\FrontOrderData as BaseFrontOrderData;

/**
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @property \App\Model\Order\Item\OrderItemData[] $itemsWithoutTransportAndPayment
 * @property \App\Model\Administrator\Administrator|null $createdAsAdministrator
 * @property \App\Model\Order\Item\OrderItemData|null $orderPayment
 * @property \App\Model\Order\Item\OrderItemData|null $orderTransport
 * @method \App\Model\Order\Item\OrderItemData[] getNewItemsWithoutTransportAndPayment()
 */
class FrontOrderData extends BaseFrontOrderData
{
}
