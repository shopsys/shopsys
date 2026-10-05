<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\Exception\TransportPriceNotFoundException;

class TransportPriceFacade
{
    public function getTransportPriceOnDomainByTransportAndClosestWeight(
        int $domainId,
        Transport $transport,
        int $cartTotalWeight,
    ): TransportPrice {
        $applicableTransportPrices = array_filter(
            $transport->getPrices(),
            static fn (TransportPrice $transportPrice): bool => $transportPrice->getDomainId() === $domainId
                && ($transportPrice->getMaxWeight() === null || $transportPrice->getMaxWeight() >= $cartTotalWeight),
        );

        if ($applicableTransportPrices === []) {
            $message = sprintf('Transport price with domain ID "%d", transport ID "%d", and cart total weight %dg not found.', $domainId, $transport->getId(), $cartTotalWeight);

            throw new TransportPriceNotFoundException($message);
        }

        usort(
            $applicableTransportPrices,
            static fn (TransportPrice $firstTransportPrice, TransportPrice $secondTransportPrice): int => ($firstTransportPrice->getMaxWeight() ?? PHP_INT_MAX) <=> ($secondTransportPrice->getMaxWeight() ?? PHP_INT_MAX),
        );

        return array_first($applicableTransportPrices);
    }
}
