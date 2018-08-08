<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class TransportPriceCalculation
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
        Transport $transport,
        Currency $currency,
        Price $productsPrice,
        $domainId
    ): \Shopsys\FrameworkBundle\Model\Pricing\Price {
        if ($this->isFree($productsPrice, $domainId)) {
            return new Price(0, 0);
        }

        return $this->calculateIndependentPrice($transport, $currency);
    }

    public function calculateIndependentPrice(
        Transport $transport,
        Currency $currency
    ): \Shopsys\FrameworkBundle\Model\Pricing\Price {
        return $this->basePriceCalculation->calculateBasePrice(
            $transport->getPrice($currency)->getPrice(),
            $this->pricingSetting->getInputPriceType(),
            $transport->getVat()
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport[] $transports
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Price[]
     */
    public function getCalculatedPricesIndexedByTransportId(
        array $transports,
        Currency $currency,
        Price $productsPrice,
        $domainId
    ): array {
        $transportsPricesByTransportId = [];
        foreach ($transports as $transport) {
            $transportsPricesByTransportId[$transport->getId()] = $this->calculatePrice(
                $transport,
                $currency,
                $productsPrice,
                $domainId
            );
        }

        return $transportsPricesByTransportId;
    }
}
