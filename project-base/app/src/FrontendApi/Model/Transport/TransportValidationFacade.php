<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Transport;

use App\FrontendApi\Model\Cart\CartFacade;
use App\FrontendApi\Model\Transport\Exception\InvalidTransportPaymentCombinationException;
use App\FrontendApi\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use App\FrontendApi\Model\Transport\Exception\TransportPriceChangedException;
use App\FrontendApi\Model\Transport\Exception\TransportWeightLimitExceededException;
use App\Model\Cart\Cart;
use App\Model\Order\Preview\OrderPreviewFactory;
use App\Model\Store\StoreFacade;
use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class TransportValidationFacade
{
    /**
     * @param \App\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        private StoreFacade $storeFacade,
        private Domain $domain,
        private CurrencyFacade $currencyFacade,
        private OrderPreviewFactory $orderPreviewFactory,
        private CurrentCustomerUser $currentCustomerUser,
        private CartFacade $cartFacade,
    ) {
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    public function checkPersonalPickupStoreAvailability(Transport $transport, ?string $pickupPlaceIdentifier): void
    {
        if ($pickupPlaceIdentifier === null || $transport->isPacketery()) {
            return;
        }

        $this->storeFacade->getByUuidEnabledOnDomain(
            $pickupPlaceIdentifier,
            $this->domain->getId(),
        );
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    public function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        if ($transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cart->getTotalWeight()) {
            throw new TransportWeightLimitExceededException();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param \App\Model\Cart\Cart $cart
     */
    public function checkTransportPrice(Transport $transport, Cart $cart): void
    {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        /** @var \App\Model\Customer\User\CustomerUser $currentCustomerUser */
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $orderPreview = $this->orderPreviewFactory->create(
            $currency,
            $domainId,
            $cart->getQuantifiedProducts(),
            $transport,
            $cart->getPayment(),
            $currentCustomerUser,
            null,
            null,
            $cart->getFirstAppliedPromoCode(),
        );

        $calculatedTransportPrice = $orderPreview->getTransportPrice();

        $transportWatchedPrice = $cart->getTransportWatchedPrice();

        if ($transportWatchedPrice === null || ($calculatedTransportPrice !== null && !$calculatedTransportPrice->getPriceWithVat()->equals($transportWatchedPrice))) {
            throw new TransportPriceChangedException($calculatedTransportPrice);
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    public function checkRequiredPickupPlaceIdentifier(Transport $transport, ?string $pickupPlaceIdentifier): void
    {
        if (($transport->isPersonalPickup() || $transport->isPacketery()) && $pickupPlaceIdentifier === null) {
            throw new MissingPickupPlaceIdentifierException();
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     */
    public function checkTransportPaymentRelation(Transport $transport, ?string $cartUuid): void
    {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $payment = $cart->getPayment();

        if ($payment === null || in_array($payment, $transport->getPayments(), true)) {
            return;
        }

        throw new InvalidTransportPaymentCombinationException();
    }
}
