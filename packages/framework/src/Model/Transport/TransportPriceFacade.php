<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportPriceFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceRepository $transportPriceRepository
     */
    public function __construct(
        protected readonly TransportPriceRepository $transportPriceRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param int $cartTotalWeight
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function getTransportPriceOnDomainByTransportAndClosestWeight(
        int $domainId,
        Transport $transport,
        int $cartTotalWeight,
    ): TransportPrice {
        return $this->transportPriceRepository->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight);
    }
}
