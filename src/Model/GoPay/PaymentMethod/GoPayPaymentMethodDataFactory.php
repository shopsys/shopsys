<?php

declare(strict_types=1);

namespace App\Model\GoPay\PaymentMethod;

class GoPayPaymentMethodDataFactory
{
    /**
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData
     */
    public function create(): GoPayPaymentMethodData
    {
        return new GoPayPaymentMethodData();
    }

    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $paymentMethod
     * @return \App\Model\GoPay\PaymentMethod\GoPayPaymentMethodData
     */
    public function createFromGoPayPaymentMethod(GoPayPaymentMethod $paymentMethod): GoPayPaymentMethodData
    {
        $goPayPaymentMethodData = $this->create();

        $goPayPaymentMethodData->identifier = $paymentMethod->getIdentifier();
        $goPayPaymentMethodData->name = $paymentMethod->getName();
        $goPayPaymentMethodData->currency = $paymentMethod->getCurrency();
        $goPayPaymentMethodData->imageNormalUrl = $paymentMethod->getImageNormalUrl();
        $goPayPaymentMethodData->imageLargeUrl = $paymentMethod->getImageLargeUrl();
        $goPayPaymentMethodData->paymentGroup = $paymentMethod->getPaymentGroup();

        return $goPayPaymentMethodData;
    }
}
