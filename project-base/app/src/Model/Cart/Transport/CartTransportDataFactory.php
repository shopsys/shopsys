<?php

declare(strict_types=1);

namespace App\Model\Cart\Transport;

use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Transport\Transport;
use App\Model\Transport\TransportFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;

class CartTransportDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation $transportPriceCalculation
     */
    public function __construct(
        private Domain $domain,
        private TransportFacade $transportFacade,
        private CurrentCustomerUser $currentCustomerUser,
        private CurrencyFacade $currencyFacade,
        private OrderPreviewFactory $orderPreviewFactory,
        private TransportPriceCalculation $transportPriceCalculation,
    ) {
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param string $transportUuid
     * @param string|null $pickupPlaceIdentifier
     * @return \App\Model\Cart\Transport\CartTransportData
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
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Component\Money\Money
     */
    private function getTransportWatchedPriceWithVat(int $domainId, Cart $cart, Transport $transport): Money
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
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
