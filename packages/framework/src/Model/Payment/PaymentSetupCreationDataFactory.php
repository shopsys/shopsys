<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationDataFactory
{
    public function createInstance(): PaymentSetupCreationData
    {
        return new PaymentSetupCreationData();
    }
}
