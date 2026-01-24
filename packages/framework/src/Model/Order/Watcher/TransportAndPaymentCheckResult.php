<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

class TransportAndPaymentCheckResult
{
    public function __construct(
        protected bool $transportPriceChanged,
        protected bool $paymentPriceChanged,
    ) {
    }

    public function isTransportPriceChanged(): bool
    {
        return $this->transportPriceChanged;
    }

    public function isPaymentPriceChanged(): bool
    {
        return $this->paymentPriceChanged;
    }
}
