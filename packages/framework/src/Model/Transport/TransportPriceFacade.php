<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

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
        Currency $currency,
    ): TransportPrice {
        return $this->transportPriceRepository->getTransportPriceOnDomainByTransportAndClosestWeight($domainId, $transport, $cartTotalWeight, $currency);
    }
}
