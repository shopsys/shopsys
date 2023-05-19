<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

interface PaymentFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $data
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public function create(PaymentData $data): Payment;
}
