<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;

class CartTransportDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider $transportPriceProvider
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly TransportFacade $transportFacade,
        protected readonly TransportPriceProvider $transportPriceProvider,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param string $transportUuid
     * @param string|null $pickupPlaceIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Transport\CartTransportData
     */
    public function create(
        Cart $cart,
        string $transportUuid,
        ?string $pickupPlaceIdentifier,
    ): CartTransportData {
        $domainId = $this->domain->getId();
        $transport = $this->transportFacade->getEnabledOnDomainByUuid($transportUuid, $domainId);
        $watchedPriceWithVat = $this->getTransportWatchedPriceWithVat($domainId, $cart, $transport);

        $cartTransportData = new CartTransportData();
        $cartTransportData->transport = $transport;
        $cartTransportData->watchedPrice = $watchedPriceWithVat;
        $cartTransportData->pickupPlaceIdentifier = $pickupPlaceIdentifier;

        return $cartTransportData;
    }

    /**
     * @param int $domainId
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    protected function getTransportWatchedPriceWithVat(int $domainId, Cart $cart, Transport $transport): Money
    {
        return $this->transportPriceProvider->getTransportPrice(
            $cart,
            $transport,
            $this->domain->getDomainConfigById($domainId),
        )->getPriceWithVat();
    }
}
