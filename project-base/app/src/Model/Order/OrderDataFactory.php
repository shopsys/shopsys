<?php

declare(strict_types=1);

namespace App\Model\Order;

use Override;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method __construct(\App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory, \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum $orderItemTypeEnum, \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestDataFactory $withdrawalRequestDataFactory, \Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequestFacade $withdrawalRequestFacade, \Psr\Clock\ClockInterface $clock)
 * @method \App\Model\Order\OrderData create()
 * @method \App\Model\Order\OrderData createFromOrder(\App\Model\Order\Order $order)
 * @method \App\Model\Order\OrderData fillZeroPrices(\App\Model\Order\OrderData $orderData)
 * @method void fillFromOrder(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method void fillDeliveryAddressFromOrder(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 * @method void fillItemsFromOrder(\App\Model\Order\OrderData $orderData, \App\Model\Order\Order $order)
 */
class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @return \App\Model\Order\OrderData
     */
    #[Override]
    protected function createInstance(): BaseOrderData
    {
        return new OrderData();
    }
}
