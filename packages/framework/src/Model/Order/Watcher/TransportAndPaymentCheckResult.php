<?php

namespace Shopsys\FrameworkBundle\Model\Order\Watcher;

class TransportAndPaymentCheckResult
{
    /**
     * @var bool
     */
    protected $transportPriceChanged;

    /**
     * @var bool
     */
    protected $paymentPriceChanged;

    /**
     * @param bool $transportPriceChanged
     * @param bool $paymentPriceChanged
     */
    public function __construct(
        $transportPriceChanged,
        $paymentPriceChanged
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
