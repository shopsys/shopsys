<?php

declare(strict_types=1);

namespace App\Model\Payment;

use Shopsys\FrameworkBundle\Model\Payment\PaymentRepository as BasePaymentRepository;
use App\Model\GoPay\PaymentMethod\GoPayPaymentMethod;

class PaymentRepository extends BasePaymentRepository
{
    /**
     * @param \App\Model\GoPay\PaymentMethod\GoPayPaymentMethod $goPayPaymentMethod
     * @return \App\Model\Payment\Payment[]
     */
    public function getByGoPayPaymentMethod(GoPayPaymentMethod $goPayPaymentMethod): array
    {
        return $this->getPaymentRepository()->findBy(['goPayPaymentMethod' => $goPayPaymentMethod]);
    }
}
