<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Payment;

class PaymentSetupCreationData
{
    /**
     * @var mixed[]
     */
    private array $goPayCreatePaymentSetup;

    /**
     * @return mixed[]
     */
    public function getGoPayCreatePaymentSetup(): array
    {
        return $this->goPayCreatePaymentSetup;
    }

    /**
     * @param mixed[] $goPayCreatePaymentSetup
     */
    public function setGoPayCreatePaymentSetup(array $goPayCreatePaymentSetup): void
    {
        $this->goPayCreatePaymentSetup = $goPayCreatePaymentSetup;
    }
}
