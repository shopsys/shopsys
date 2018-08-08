<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

interface PaymentFactoryInterface
{

    public function create(PaymentData $data): Payment;
}
