<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class CartTransportDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly TransportFacade $transportFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly TransportPriceCalculation $transportPriceCalculation,
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
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            $cart->getPayment(),
            $customerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        $watchedPrice = $this->transportPriceCalculation->calculatePrice(
            $transport,
            $currency,
            $orderPreview->getProductsPrice(),
            $domainId,
        );

        return $watchedPrice->getPriceWithVat();
    }
}
