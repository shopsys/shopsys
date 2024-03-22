<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Store\StoreFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\InvalidTransportPaymentCombinationException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\MissingPickupPlaceIdentifierException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportPriceChangedException;
use Shopsys\FrontendApiBundle\Model\Transport\Exception\TransportWeightLimitExceededException;

class TransportValidationFacade
{
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    public function checkPersonalPickupStoreAvailability(Transport $transport, ?string $pickupPlaceIdentifier): void
    {
        if ($pickupPlaceIdentifier === null || $transport->isPacketery()) {
            return;
        }

        $this->storeFacade->getByUuidAndDomainId(
            $pickupPlaceIdentifier,
            $this->domain->getId(),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkTransportWeightLimit(Transport $transport, Cart $cart): void
    {
        if ($transport->getMaxWeight() !== null && $transport->getMaxWeight() < $cart->getTotalWeight()) {
            throw new TransportWeightLimitExceededException();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function checkTransportPrice(Transport $transport, Cart $cart): void
    {
        $domainId = $this->domain->getId();
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
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
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $pickupPlaceIdentifier
     */
    public function checkRequiredPickupPlaceIdentifier(Transport $transport, ?string $pickupPlaceIdentifier): void
    {
        if (($transport->isPersonalPickup() || $transport->isPacketery()) && $pickupPlaceIdentifier === null) {
            throw new MissingPickupPlaceIdentifierException();
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param string|null $cartUuid
     */
    public function checkTransportPaymentRelation(Transport $transport, ?string $cartUuid): void
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();
        $cart = $this->cartApiFacade->getCartCreateIfNotExists($customerUser, $cartUuid);
        $payment = $cart->getPayment();

        if ($payment === null || in_array($payment, $transport->getPayments(), true)) {
            return;
        }

        throw new InvalidTransportPaymentCombinationException();
    }
}
