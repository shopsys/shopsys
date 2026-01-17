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
        Currency $currency,
        PriceInterface $productsPrice,
        int $domainId,
        bool $forceFreePayment,
    ): PriceInterface {
        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice, $domainId, $forceFreePayment)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($payment, $currency, $domainId);
    }

    public function calculateIndependentPrice(Payment $payment, Currency $currency, int $domainId): PriceInterface
    {
        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $payment->getPrice($domainId)->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $payment->getPaymentDomain($domainId)->getVat(),
            $currency,
        );
    }
}
