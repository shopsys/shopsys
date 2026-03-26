<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

class PaymentSetupCreationData
{
    protected array $goPayCreatePaymentSetup;

    /**
     * Validity hash is attached to the PayOrder response so the frontend receives
     * both the GoPay gateway URL and the hash in a single round-trip.
     * The hash is used to authorize access to the payment status page.
     */
    protected ?string $orderPaymentStatusPageValidityHash = null;

    public function getGoPayCreatePaymentSetup(): array
    {
        return $this->goPayCreatePaymentSetup;
    }

    public function setGoPayCreatePaymentSetup(array $goPayCreatePaymentSetup): void
    {
        $this->goPayCreatePaymentSetup = $goPayCreatePaymentSetup;
    }

    public function getOrderPaymentStatusPageValidityHash(): ?string
    {
        return $this->orderPaymentStatusPageValidityHash;
    }

    public function setOrderPaymentStatusPageValidityHash(?string $orderPaymentStatusPageValidityHash): void
    {
        $this->orderPaymentStatusPageValidityHash = $orderPaymentStatusPageValidityHash;
    }
}
