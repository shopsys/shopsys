<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentFacade;

class OrderPaymentsConfigFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentFacade $paymentFacade
     */
    public function __construct(
        protected readonly PaymentFacade $paymentFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrontendApiBundle\Model\Order\OrderPaymentsConfig
     */
    public function createForOrder(Order $order): OrderPaymentsConfig
    {
        $payments = $this->paymentFacade->getVisibleForOrder($order);

        $currentPayment = $order->getPayment();
        $availablePayments = array_filter(
            $payments,
            static fn (Payment $payment) => $payment->getId() !== $currentPayment->getId(),
        );

        return new OrderPaymentsConfig($currentPayment, $availablePayments);
    }
}
