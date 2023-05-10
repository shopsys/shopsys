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
 * @property \App\Model\Order\Status\OrderStatus|null $status
 * @property \App\Model\Customer\DeliveryAddress $deliveryAddress
 */
class FrontOrderData extends BaseFrontOrderData
{
    /**
     * @var \App\Model\Store\Store|null
     */
    public $personalPickupStore;

    /**
     * @var \App\Model\GoPay\BankSwift\GoPayBankSwift
     */
    public $goPayBankSwift;

    /**
     * @var string|null
     */
    public $password;

    /**
     * @var bool
     */
    public $register = false;
}
