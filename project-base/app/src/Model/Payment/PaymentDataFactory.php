<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory as BasePaymentDataFactory;

/**
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

    /**
     * @return \App\Model\Payment\PaymentData
     */
    public function create(): BasePaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillNew($paymentData);

        $paymentData->hiddenByGoPay = false;

        return $paymentData;
    }

    /**
     * @param \App\Model\Payment\Payment $payment
     * @return \App\Model\Payment\PaymentData
     */
    public function createFromPayment(BasePayment $payment): BasePaymentData
    {
        $paymentData = $this->createInstance();
        $this->fillFromPayment($paymentData, $payment);

        $paymentData->type = $payment->getType();
        $paymentData->goPayPaymentMethod = $payment->getGoPayPaymentMethod();
        $paymentData->hiddenByGoPay = $payment->isHiddenByGoPay();

        return $paymentData;
    }
}
