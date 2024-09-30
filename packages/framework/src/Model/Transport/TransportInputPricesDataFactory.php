<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportInputPricesDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     */
    public function __construct(protected readonly VatFacade $vatFacade)
    {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData
     */
    public function create(int $domainId): TransportInputPricesData
    {
        $transportInputPriceData = $this->createInstance();
        $transportInputPriceData->vat = $this->vatFacade->getDefaultVatForDomain($domainId);
        $priceWithLimitData = $this->createPriceWithLimitDataInstance();
        $priceWithLimitData->price = Money::zero();
        $transportInputPriceData->pricesWithLimits[] = $priceWithLimitData;

        return $transportInputPriceData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData
     */
    protected function createInstance(): TransportInputPricesData
    {
        return new TransportInputPricesData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData
     */
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\PriceWithLimitData
     */
    public function createPriceWithLimitDataInstance(): PriceWithLimitData
    {
        return new PriceWithLimitData();
    }
}
