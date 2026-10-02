<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;

class CartTransportDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly TransportFacade $transportFacade,
        protected readonly TransportPriceProvider $transportPriceProvider,
    ) {
    }

    public function create(
        Cart $cart,
        string $transportUuid,
        ?string $pickupPlaceIdentifier,
    ): CartTransportData {
        $domainId = $this->domain->getId();
        $transport = $this->transportFacade->getEnabledOnDomainByUuid($transportUuid, $domainId);
        $watchedPrice = $this->getTransportWatchedPrice($domainId, $cart, $transport);

        $cartTransportData = new CartTransportData();
        $cartTransportData->transport = $transport;
        $cartTransportData->watchedPrice = $watchedPrice->getPriceWithVat();
        $cartTransportData->watchedPriceWithoutVat = $watchedPrice->getPriceWithoutVat();
        $cartTransportData->pickupPlaceIdentifier = $pickupPlaceIdentifier;

        return $cartTransportData;
    }

    protected function getTransportWatchedPrice(int $domainId, Cart $cart, Transport $transport): PriceInterface
    {
        return $this->transportPriceProvider->getTransportPrice(
            $cart,
            $transport,
            $this->domain->getDomainConfigById($domainId),
        );
    }
}
