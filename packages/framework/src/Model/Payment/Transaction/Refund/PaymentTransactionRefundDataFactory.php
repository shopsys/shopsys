<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund;

use Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction;

class PaymentTransactionRefundDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Transaction\PaymentTransaction $paymentTransaction
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData
     */
    public function createFromPaymentTransaction(PaymentTransaction $paymentTransaction): PaymentTransactionRefundData
    {
        $paymentTransactionRefundData = $this->createInstance();
        $paymentTransactionRefundData->refundedAmount = $paymentTransaction->getRefundedAmount();

        return $paymentTransactionRefundData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\Transaction\Refund\PaymentTransactionRefundData
     */
    protected function createInstance(): PaymentTransactionRefundData
    {
        return new PaymentTransactionRefundData();
    }
}
