<?php

declare(strict_types=1);

namespace App\Model\Payment\Transaction;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PaymentTransactionDataFactory
{
    /**
     * @return \App\Model\Payment\Transaction\PaymentTransactionData
     */
    public function create(): PaymentTransactionData
    {
        $paymentTransaction = new PaymentTransactionData();
        $paymentTransaction->refundedAmount = Money::zero();

        return $paymentTransaction;
    }

    /**
     * @param \App\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @return \App\Model\Payment\Transaction\PaymentTransactionData
     */
    public function createFromPaymentTransaction(PaymentTransaction $paymentTransaction): PaymentTransactionData
    {
        $paymentTransactionData = $this->create();
        $paymentTransactionData->order = $paymentTransaction->getOrder();
        $paymentTransactionData->payment = $paymentTransaction->getPayment();
        $paymentTransactionData->paidAmount = $paymentTransaction->getPaidAmount();
        $paymentTransactionData->externalPaymentIdentifier = $paymentTransaction->getExternalPaymentIdentifier();
        $paymentTransactionData->externalPaymentStatus = $paymentTransaction->getExternalPaymentStatus();
        $paymentTransactionData->refundedAmount = $paymentTransaction->getRefundedAmount();

        return $paymentTransactionData;
    }
}
