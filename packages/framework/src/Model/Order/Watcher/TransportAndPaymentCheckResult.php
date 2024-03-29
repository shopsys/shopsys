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
        $transportPriceChanged,
        $paymentPriceChanged,
    ) {
        $this->transportPriceChanged = $transportPriceChanged;
        $this->paymentPriceChanged = $paymentPriceChanged;
    }

    /**
     * @return bool
     */
    public function isTransportPriceChanged()
    {
        return $this->transportPriceChanged;
    }

    /**
     * @return bool
     */
    public function isPaymentPriceChanged()
    {
        return $this->paymentPriceChanged;
    }
}
