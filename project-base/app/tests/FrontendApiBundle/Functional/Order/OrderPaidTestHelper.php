<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Order;

use App\Model\Order\Order;
use GoPay\Definition\Response\PaymentStatus;
use Shopsys\FrameworkBundle\Model\Order\OrderPaidStatusFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;

final class OrderPaidTestHelper
{
    public function __construct(
        private readonly PaymentTransactionFacade $paymentTransactionFacade,
        private readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        private readonly OrderPaidStatusFacade $orderPaidStatusFacade,
    ) {
    }

    public function markOrderAsPaidByPaymentTransactions(Order $order): void
    {
        foreach ($order->getPaymentTransactions() as $paymentTransaction) {
            $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
            $paymentTransactionData->externalPaymentStatus = PaymentStatus::PAID;
            $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
        }

        $this->orderPaidStatusFacade->refreshOrderPaidStatusByPaymentTransactions($order);
    }
}
