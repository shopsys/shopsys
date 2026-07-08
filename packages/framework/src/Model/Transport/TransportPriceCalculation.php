<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class TransportPriceCalculation
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly TransportPriceFacade $transportPriceFacade,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
    ) {
    }

    public function calculatePrice(
        Transport $transport,
        PriceInterface $productsPrice,
        int $domainId,
        int $cartTotalWeight,
        bool $forceFreeTransport,
        Currency $currency,
    ): PriceInterface {
        $transportPrice = $this->transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight, $currency);

        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice, $domainId, $forceFreeTransport, $currency)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($transportPrice);
    }

    public function calculateIndependentPrice(TransportPrice $transportPrice): PriceInterface
    {
        $domainId = $transportPrice->getDomainId();
        $currency = $transportPrice->getCurrency();
        $vat = $transportPrice->getTransport()->getTransportDomain($domainId)->getVat();

        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $transportPrice->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $currency->getRoundingType(),
            $currency->getRoundingPlacesPriceWithoutVat(),
            $currency,
        );
    }
}
