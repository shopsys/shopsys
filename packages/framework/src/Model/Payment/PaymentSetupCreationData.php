<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationData
{
    /**
     * @var array<string, mixed>
     */
    protected array $goPayCreatePaymentSetup;

    /**
     * @return array<string, mixed>
     */
    public function getGoPayCreatePaymentSetup(): array
    {
        return $this->goPayCreatePaymentSetup;
    }

    /**
     * @param array<string, mixed> $goPayCreatePaymentSetup
     */
    public function setGoPayCreatePaymentSetup(array $goPayCreatePaymentSetup): void
    {
        $this->goPayCreatePaymentSetup = $goPayCreatePaymentSetup;
    }
}
