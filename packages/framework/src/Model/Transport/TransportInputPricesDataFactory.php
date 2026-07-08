<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportInputPricesDataFactory
{
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
    ) {
    }

    public function create(int $domainId): TransportInputPricesData
    {
        $transportInputPriceData = $this->createInstance();
        $transportInputPriceData->vat = $this->vatFacade->getDefaultVatForDomain($domainId);
        $priceWithLimitData = $this->createPriceWithLimitDataInstance();

        foreach ($this->domain->getDomainConfigById($domainId)->getCurrencyCodes() as $currencyCode) {
            $priceWithLimitData->pricesByCurrencyCode[$currencyCode] = Money::zero();
            $priceWithLimitData->transportPriceIdsByCurrencyCode[$currencyCode] = null;
        }
        $transportInputPriceData->pricesWithLimits[] = $priceWithLimitData;

        return $transportInputPriceData;
    }

    protected function createInstance(): TransportInputPricesData
    {
        return new TransportInputPricesData();
    }

    public function createFromTransport(Transport $transport, int $domainId): TransportInputPricesData
    {
        $transportInputPriceData = $this->createInstance();

        $transportInputPriceData->pricesWithLimits = [];
        $currencyCodes = $this->domain->getDomainConfigById($domainId)->getCurrencyCodes();

        $transportPricesByMaxWeight = [];

        foreach ($transport->getPricesByDomainId($domainId) as $transportPrice) {
            $transportPricesByMaxWeight[$transportPrice->getMaxWeight() ?? PHP_INT_MAX][$transportPrice->getCurrency()->getCode()] = $transportPrice;
        }

        foreach ($transportPricesByMaxWeight as $transportPricesByCurrencyCode) {
            $priceWithLimitData = $this->createPriceWithLimitDataInstance();

            foreach ($currencyCodes as $currencyCode) {
                $transportPrice = $transportPricesByCurrencyCode[$currencyCode] ?? null;
                $priceWithLimitData->pricesByCurrencyCode[$currencyCode] = $transportPrice?->getPrice();
                $priceWithLimitData->transportPriceIdsByCurrencyCode[$currencyCode] = $transportPrice?->getId();
                $priceWithLimitData->maxWeight ??= $transportPrice?->getMaxWeight();
            }

            $transportInputPriceData->pricesWithLimits[] = $priceWithLimitData;
        }
        $transportInputPriceData->vat = $transport->getTransportDomain($domainId)->getVat();

        return $transportInputPriceData;
    }

    public function createPriceWithLimitDataInstance(): PriceWithLimitData
    {
        return new PriceWithLimitData();
    }
}
