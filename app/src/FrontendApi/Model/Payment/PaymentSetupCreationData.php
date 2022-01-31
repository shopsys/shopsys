<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Payment;

class PaymentSetupCreationData
{
    /**
     * @var array
     */
    private array $goPayCreatePaymentSetup;

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
