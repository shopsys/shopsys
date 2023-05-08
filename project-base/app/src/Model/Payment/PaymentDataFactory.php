<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory as BasePaymentDataFactory;

/**
 * @method \App\Model\Payment\PaymentData create()
 * @method \App\Model\Payment\PaymentData createFromPayment(\App\Model\Payment\Payment $payment)
 * @method fillNew(\App\Model\Payment\PaymentData $paymentData)
 * @method fillFromPayment(\App\Model\Payment\PaymentData $paymentData, \App\Model\Payment\Payment $payment)
 */
class PaymentDataFactory extends BasePaymentDataFactory
{
    /**
     * @return \App\Model\Payment\PaymentData
     */
    protected function createInstance(): BasePaymentData
    {
        $paymentData = new PaymentData();
        $paymentData->image = $this->imageUploadDataFactory->create();

        return $paymentData;
    }
}
