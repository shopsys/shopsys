<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod;

class GoPayPaymentMethodDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData
     */
    public function createInstance(): GoPayPaymentMethodData
    {
        return new GoPayPaymentMethodData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethod $paymentMethod
     * @return \Shopsys\FrameworkBundle\Model\GoPay\PaymentMethod\GoPayPaymentMethodData
     */
    public function createFromGoPayPaymentMethod(GoPayPaymentMethod $paymentMethod): GoPayPaymentMethodData
    {
        $goPayPaymentMethodData = $this->createInstance();

        $goPayPaymentMethodData->identifier = $paymentMethod->getIdentifier();
        $goPayPaymentMethodData->name = $paymentMethod->getName();
        $goPayPaymentMethodData->currency = $paymentMethod->getCurrency();
        $goPayPaymentMethodData->imageNormalUrl = $paymentMethod->getImageNormalUrl();
        $goPayPaymentMethodData->imageLargeUrl = $paymentMethod->getImageLargeUrl();
        $goPayPaymentMethodData->paymentGroup = $paymentMethod->getPaymentGroup();
        $goPayPaymentMethodData->domainId = $paymentMethod->getDomainId();
        $goPayPaymentMethodData->available = $paymentMethod->isAvailable();

        return $goPayPaymentMethodData;
    }
}
