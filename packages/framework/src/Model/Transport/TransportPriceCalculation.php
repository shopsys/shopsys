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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation $basePriceCalculation
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PricingSetting $pricingSetting
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade $transportPriceFacade
     * @param \Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade
     */
    public function __construct(
        protected readonly BasePriceCalculation $basePriceCalculation,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly TransportPriceFacade $transportPriceFacade,
        protected readonly FreeTransportAndPaymentFacade $freeTransportAndPaymentFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface $productsPrice
     * @param int $domainId
     * @param int $cartTotalWeight
     * @param bool $forceFreeTransport
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculatePrice(
        Transport $transport,
        PriceInterface $productsPrice,
        int $domainId,
        int $cartTotalWeight,
        bool $forceFreeTransport,
    ): PriceInterface {
        $transportPrice = $this->transportPriceFacade->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight);

        if ($this->freeTransportAndPaymentFacade->isFree($productsPrice->getPriceWithVat(), $domainId, $forceFreeTransport)) {
            return Price::zero();
        }

        return $this->calculateIndependentPrice($transportPrice);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPrice $transportPrice
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function calculateIndependentPrice(TransportPrice $transportPrice): PriceInterface
    {
        $domainId = $transportPrice->getDomainId();
        $defaultCurrencyForDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(
            $domainId,
        );
        $vat = $transportPrice->getTransport()->getTransportDomain($domainId)->getVat();

        return $this->basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $transportPrice->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $vat,
            $defaultCurrencyForDomain,
        );
    }
}
