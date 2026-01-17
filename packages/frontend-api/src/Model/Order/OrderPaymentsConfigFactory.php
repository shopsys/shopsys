<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;

class OrderPaymentsConfigFactory
{
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    public function createForOrder(Order $order): OrderPaymentsConfig
    {
        $payments = $this->paymentFacade->getVisibleForOrder($order);

        $currentPayment = $order->isMaxTransactionCountReached() ? null : $order->getPayment();

        if ($currentPayment !== null && !in_array($currentPayment, $payments, true)) {
            $currentPayment = null;
        }

        $availablePayments = array_filter(
            $payments,
            static function (Payment $payment) use ($order, $currentPayment) {
                if ($payment->getId() === $currentPayment?->getId()) {
                    return false;
                }

                if ($order->isMaxTransactionCountReached()) {
                    return !$payment->isGatewayPayment();
                }

                return true;
            },
        );

        return new OrderPaymentsConfig($currentPayment, $availablePayments);
    }
}
