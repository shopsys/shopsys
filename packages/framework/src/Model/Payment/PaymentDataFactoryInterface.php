<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

interface PaymentDataFactoryInterface
{
    public function create(): PaymentData;

    public function createFromPayment(Payment $payment): PaymentData;
}
