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

    public function isTransportPriceChanged()
    {
        return $this->transportPriceChanged;
    }

    public function isPaymentPriceChanged()
    {
        return $this->paymentPriceChanged;
    }
}
