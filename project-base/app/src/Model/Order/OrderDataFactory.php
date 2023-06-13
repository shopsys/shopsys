<?php

declare(strict_types=1);

namespace App\Model\Order;

use App\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Order\Order as BaseOrder;
use Shopsys\FrameworkBundle\Model\Order\OrderData as BaseOrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory as BaseOrderDataFactory;

/**
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 */
class OrderDataFactory extends BaseOrderDataFactory
{
    /**
     * @param \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
     * @param \App\Model\Payment\Transaction\Refund\PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory
     */
    public function __construct(
        OrderItemDataFactoryInterface $orderItemDataFactory,
        private readonly PaymentTransactionRefundDataFactory $paymentTransactionRefundDataFactory,
    ) {
        parent::__construct($orderItemDataFactory);
    }

    /**
     * @return \App\Model\Order\OrderData
     */
    public function create(): BaseOrderData
    {
        return $this->createInstance();
    }

    /**
     * @return \App\Model\Order\OrderData
     */
    protected function createInstance(): BaseOrderData
    {
        return new OrderData();
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Order\OrderData
     */
    public function createFromOrder(BaseOrder $order): BaseOrderData
    {
        $orderData = new OrderData();
        $this->fillFromOrder($orderData, $order);

        return $orderData;
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \App\Model\Order\Order $order
     */
    protected function fillFromOrder(BaseOrderData $orderData, BaseOrder $order): void
    {
        parent::fillFromOrder($orderData, $order);

        $orderData->gtmCoupon = $order->getGtmCoupon();
        $orderData->trackingNumber = $order->getTrackingNumber();
        $orderData->goPayBankSwift = $order->getGoPayBankSwift();
        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $orderData->paymentTransactionRefunds[$paymentTransaction->getId()] = $this->paymentTransactionRefundDataFactory->createFromPaymentTransaction($paymentTransaction);
        }
    }
}
