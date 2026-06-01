<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Payment\Service\PaymentServiceFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionDataFactory;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionFacade;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\PaymentTransactionNotRefundableException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundAmountGreaterThanRefundableAmountException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundAmountNotPositiveException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundedAmountGreaterThanPaidAmountException;
use Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\Exception\RefundedAmountNegativeException;

class PaymentTransactionRefundFacade
{
    public function __construct(
        protected readonly PaymentTransactionFacade $paymentTransactionFacade,
        protected readonly PaymentTransactionDataFactory $paymentTransactionDataFactory,
        protected readonly PaymentServiceFacade $paymentServiceFacade,
    ) {
    }

    public function changeManualRefundedAmount(
        PaymentTransaction $paymentTransaction,
        Money $refundedAmount,
    ): void {
        if ($refundedAmount->isNegative()) {
            throw new RefundedAmountNegativeException();
        }

        if ($paymentTransaction->getPaidAmount()->isLessThan($refundedAmount)) {
            throw new RefundedAmountGreaterThanPaidAmountException();
        }

        $paymentTransactionData = $this->paymentTransactionDataFactory->createFromPaymentTransaction($paymentTransaction);
        $paymentTransactionData->refundedAmount = $refundedAmount;
        $this->paymentTransactionFacade->edit($paymentTransaction->getId(), $paymentTransactionData);
    }

    public function executeOnlineRefund(
        PaymentTransaction $paymentTransaction,
        Money $refundAmount,
    ): bool {
        if ($paymentTransaction->getPayment() === null || !$paymentTransaction->isRefundable()) {
            throw new PaymentTransactionNotRefundableException();
        }

        if (!$refundAmount->isPositive()) {
            throw new RefundAmountNotPositiveException();
        }

        if ($paymentTransaction->getRefundableAmount()->isLessThan($refundAmount)) {
            throw new RefundAmountGreaterThanRefundableAmountException();
        }

        return $this->paymentServiceFacade->refundTransaction($paymentTransaction, $refundAmount);
    }
}
