<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;

class TransportPriceCalculation
{
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
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
    ): PriceInterface {
        $transportPrice = $this->transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight);

        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice, $domainId, $forceFreeTransport)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($transportPrice);
    }

    public function calculateIndependentPrice(TransportPrice $transportPrice): PriceInterface
    {
        $domainId = $transportPrice->getDomainId();
        $defaultCurrencyForDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(
            $domainId,
        );
        $vat = $transportPrice->getTransport()->getTransportDomain($domainId)->getVat();

        return $this->basePriceCalculation->calculateRoundedBasePrice(
            $transportPrice->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $defaultCurrencyForDomain->getRoundingType(),
            $defaultCurrencyForDomain->getRoundingPlacesPriceWithoutVat(),
        );
    }
}
