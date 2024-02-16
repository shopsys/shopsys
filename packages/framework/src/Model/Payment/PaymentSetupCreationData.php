<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationData
{
    /**
     * @var array
     */
    protected array $goPayCreatePaymentSetup;

    /**
     * @return array
     */
    public function getGoPayCreatePaymentSetup(): array
    {
        return $this->goPayCreatePaymentSetup;
    }

    /**
     * @param array $goPayCreatePaymentSetup
     */
    public function setGoPayCreatePaymentSetup(array $goPayCreatePaymentSetup): void
    {
        $this->goPayCreatePaymentSetup = $goPayCreatePaymentSetup;
    }
}
