<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentFactory implements PaymentFactoryInterface
{
    public function create(PaymentData $data): Payment
    {
        return new Payment($data);
    }
}
