<?php

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

class TransportAndPaymentCheckResult
{
    /**
     * @var bool
     */
    private $transportPriceChanged;

    /**
     * @var bool
     */
    private $paymentPriceChanged;

    public function __construct(
        bool $transportPriceChanged,
        bool $paymentPriceChanged
    ) {
        $this->transportPriceChanged = $transportPriceChanged;
        $this->paymentPriceChanged = $paymentPriceChanged;
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
