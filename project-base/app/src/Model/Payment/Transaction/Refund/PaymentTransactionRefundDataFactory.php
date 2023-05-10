<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction\Refund;

use App\Model\Payment\Transaction\PaymentTransaction;

class PaymentTransactionRefundDataFactory
{
    /**
     * @param \App\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @return \App\Model\Payment\Transaction\Refund\PaymentTransactionRefundData
     */
    public function createFromPaymentTransaction(PaymentTransaction $paymentTransaction): PaymentTransactionRefundData
    {
        $paymentTransactionRefundData = new PaymentTransactionRefundData();
        $paymentTransactionRefundData->refundedAmount = $paymentTransaction->getRefundedAmount();

        return $paymentTransactionRefundData;
    }
}
