<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationData
{
    protected array $goPayCreatePaymentSetup;

    public function getGoPayCreatePaymentSetup(): array
    {
        return $this->goPayCreatePaymentSetup;
    }

    public function setGoPayCreatePaymentSetup(array $goPayCreatePaymentSetup): void
    {
        $this->goPayCreatePaymentSetup = $goPayCreatePaymentSetup;
    }
}
