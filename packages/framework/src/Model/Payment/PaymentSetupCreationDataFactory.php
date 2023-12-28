<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentSetupCreationData
     */
    public function createInstance(): PaymentSetupCreationData
    {
        return new PaymentSetupCreationData();
    }
}
