<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\Payment as BasePayment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData as BasePaymentData;
use Shopsys\FrameworkBundle\Model\Payment\PaymentDataFactory as BasePaymentDataFactory;

class PaymentDataFactory extends BasePaymentDataFactory
{
    /**
     * @return \Shopsys\ShopBundle\Model\Payment\PaymentData
     */
    public function create(): BasePaymentData
    {
        $paymentData = new PaymentData();
        $this->fillNew($paymentData);

        return $paymentData;
    }

    /**
     * @param \Shopsys\ShopBundle\Model\Payment\Payment $payment
     * @return \Shopsys\ShopBundle\Model\Payment\PaymentData
     */
    public function createFromPayment(BasePayment $payment): BasePaymentData
    {
        $paymentData = new PaymentData();
        $this->fillFromPayment($paymentData, $payment);

        return $paymentData;
    }
}
