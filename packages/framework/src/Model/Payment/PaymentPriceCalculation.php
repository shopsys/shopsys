<?php

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class PaymentPriceCalculation
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation
     */
    private $basePriceCalculation;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting
     */
    private $pricingSetting;

    public function __construct(
        BasePriceCalculation $basePriceCalculation,
        PricingSetting $pricingSetting
    ) {
        $this->pricingSetting = $pricingSetting;
        $this->basePriceCalculation = $basePriceCalculation;
    }

    /**
     * @param int $domainId
     */
    public function calculatePrice(
        Payment $payment,
        Currency $currency,
        Price $productsPrice,
        $domainId
    ): \Shopsys\FrameworkBundle\Model\Pricing\Price {
        if ($this->isFree($productsPrice, $domainId)) {
            return new Price(0, 0);
        }

        return $this->calculateIndependentPrice($payment, $currency);
    }

    public function calculateIndependentPrice(
        Payment $payment,
        Currency $currency
    ): \Shopsys\FrameworkBundle\Model\Pricing\Price {
        return $this->basePriceCalculation->calculateBasePrice(
            $payment->getPrice($currency)->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $payment->getVat()
        );
    }

    /**
     * @param int $domainId
     */
    private function isFree(Price $productsPrice, $domainId): bool
    {
        $freeTransportAndPaymentPriceLimit = $this->pricingSetting->getFreeTransportAndPaymentPriceLimit($domainId);

        if ($freeTransportAndPaymentPriceLimit === null) {
            return false;
        }

        return $productsPrice->getPriceWithVat() >= $freeTransportAndPaymentPriceLimit;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\Payment[] $payments
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getCalculatedPricesIndexedByPaymentId(
        array $payments,
        Currency $currency,
        Price $productsPrice,
        $domainId
    ): array {
        $paymentsPricesByPaymentId = [];
        foreach ($payments as $payment) {
            $paymentsPricesByPaymentId[$payment->getId()] = $this->calculatePrice(
                $payment,
                $currency,
                $productsPrice,
                $domainId
            );
        }

        return $paymentsPricesByPaymentId;
    }
}
