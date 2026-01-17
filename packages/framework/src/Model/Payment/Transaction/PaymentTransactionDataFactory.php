<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PaymentTransactionDataFactory
{
    public function create(): PaymentTransactionData
    {
        $paymentTransaction = $this->createInstance();
        $paymentTransaction->refundedAmount = Money::zero();

        return $paymentTransaction;
    }

    public function createFromPaymentTransaction(PaymentTransaction $paymentTransaction): PaymentTransactionData
    {
        $paymentTransactionData = $this->create();
        $paymentTransactionData->order = $paymentTransaction->getOrder();
        $paymentTransactionData->payment = $paymentTransaction->getPayment();
        $paymentTransactionData->paidAmount = $paymentTransaction->getPaidAmount();
        $paymentTransactionData->externalPaymentIdentifier = $paymentTransaction->getExternalPaymentIdentifier();
        $paymentTransactionData->externalPaymentStatus = $paymentTransaction->getExternalPaymentStatus();
        $paymentTransactionData->externalPaymentSubStatus = $paymentTransaction->getExternalPaymentSubStatus();
        $paymentTransactionData->externalPaymentUrl = $paymentTransaction->getExternalPaymentUrl();
        $paymentTransactionData->refundedAmount = $paymentTransaction->getRefundedAmount();
        $paymentTransactionData->externalPaymentMethod = $paymentTransaction->getExternalPaymentMethod();

        return $paymentTransactionData;
    }

    protected function createInstance(): PaymentTransactionData
    {
        return new PaymentTransactionData();
    }
}
