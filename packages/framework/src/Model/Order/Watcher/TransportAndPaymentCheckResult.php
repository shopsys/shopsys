<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

class TransportAndPaymentCheckResult
{
    protected bool $transportPriceChanged;

    protected bool $paymentPriceChanged;

    /**
     * @param bool $transportPriceChanged
     * @param bool $paymentPriceChanged
     */
    public function __construct(
        bool $transportPriceChanged,
        bool $paymentPriceChanged,
    ) {
        $this->transportPriceChanged = $transportPriceChanged;
        $this->paymentPriceChanged = $paymentPriceChanged;
    }

    /**
     * @return bool
     */
    public function isTransportPriceChanged(): bool
    {
        return $this->transportPriceChanged;
    }

    /**
     * @return bool
     */
    public function isPaymentPriceChanged(): bool
    {
        return $this->paymentPriceChanged;
    }
}
