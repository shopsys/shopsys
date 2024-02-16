<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction;

use Shopsys\FrameworkBundle\Component\Money\Money;

class PaymentTransactionDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData
     */
    public function create(): PaymentTransactionData
    {
        $paymentTransaction = $this->createInstance();
        $paymentTransaction->refundedAmount = Money::zero();

        return $paymentTransaction;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransactionData
     */
    protected function createInstance(): PaymentTransactionData
    {
        return new PaymentTransactionData();
    }
}
