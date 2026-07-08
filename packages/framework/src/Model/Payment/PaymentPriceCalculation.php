<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Payment;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class PaymentPriceCalculation
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
    ) {
    }

    public function calculatePrice(
        Payment $payment,
        PriceInterface $productsPrice,
        int $domainId,
        Currency $currency,
        bool $forceFreePayment,
    ): PriceInterface {
        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice, $domainId, $forceFreePayment, $currency)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($payment, $domainId, $currency);
    }

    public function calculateIndependentPrice(
        Payment $payment,
        int $domainId,
        Currency $currency,
    ): PriceInterface {
        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $payment->getPrice($domainId, $currency)->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $payment->getPaymentDomain($domainId)->getVat(),
            $currency->getRoundingType(),
            $currency->getRoundingPlacesPriceWithoutVat(),
            $currency,
        );
    }
}
