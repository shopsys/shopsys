<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportPriceFacade
{
    public function __construct(
        protected readonly TransportPriceRepository $transportPriceRepository,
    ) {
    }

    public function getTransportPriceOnDomainByTransportAndClosestWeight(
        int $domainId,
        Transport $transport,
        int $cartTotalWeight,
    ): TransportPrice {
        return $this->transportPriceRepository->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight);
    }
}
