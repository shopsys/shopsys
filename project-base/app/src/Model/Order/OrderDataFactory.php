<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method __construct(\App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory, \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum $orderItemTypeEnum, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor)
 * @method \App\Model\Order\OrderData create()
 * @method \App\Model\Order\OrderData createFromOrder(\App\Model\Order\Order $order)
 * @method \App\Model\Order\OrderData fillZeroPrices(\App\Model\Order\OrderData $orderData)
 * @method fillFromOrder(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method \App\Model\Order\OrderData createFromCart(\App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 * @method \App\Model\Order\OrderData fillFromCart(\App\Model\Order\OrderData $orderData, \App\Model\Cart\Cart $cart, \Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig $domainConfig)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @return \App\Model\Order\OrderData
     */
    protected function createInstance(): BaseOrderData
    {
        return new OrderData();
    }
}
