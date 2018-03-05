<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportEditDataFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    private $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    public function __construct(
        TransportFacade $transportFacade,
        VatFacade $vatFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->vatFacade = $vatFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportEditData
     */
    public function createDefault()
    {
        $transportEditData = new TransportEditData();
        $transportEditData->transportData->vat = $this->vatFacade->getDefaultVat();

        return $transportEditData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportEditData
     */
    public function createFromTransport(Transport $transport)
    {
        $transportEditData = new TransportEditData();
        $transportData = new TransportData();
        $transportData->setFromEntity($transport, $this->transportFacade->getTransportDomainsByTransport($transport));
        $transportEditData->transportData = $transportData;

        foreach ($transport->getPrices() as $transportPrice) {
            $transportEditData->pricesByCurrencyId[$transportPrice->getCurrency()->getId()] = $transportPrice->getPrice();
        }

        return $transportEditData;
    }
}
