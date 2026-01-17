<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportInputPricesDataFactory
{
    public function __construct(protected readonly VatFacade $vatFacade)
    {
    }

    public function create(int $domainId): TransportInputPricesData
    {
        $transportInputPriceData = $this->createInstance();
        $transportInputPriceData->vat = $this->vatFacade->getDefaultVatForDomain($domainId);
        $priceWithLimitData = $this->createPriceWithLimitDataInstance();
        $priceWithLimitData->price = Money::zero();
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

        foreach ($transport->getPricesByDomainId($domainId) as $transportPrice) {
            $priceWithLimitData = $this->createPriceWithLimitDataInstance();
            $priceWithLimitData->price = $transportPrice->getPrice();
            $priceWithLimitData->maxWeight = $transportPrice->getMaxWeight();
            $priceWithLimitData->transportPriceId = $transportPrice->getId();

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
