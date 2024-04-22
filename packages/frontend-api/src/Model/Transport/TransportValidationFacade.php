<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor;
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
    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\StoreFacade $storeFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory
     */
    public function __construct(
        protected readonly StoreFacade $storeFacade,
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CartApiFacade $cartApiFacade,
        protected readonly OrderProcessor $orderProcessor,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly OrderInputFactory $orderInputFactory,
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
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $orderData = $this->orderDataFactory->create();
        $orderInput = $this->orderInputFactory->createFromCart($cart);
        $orderInput->setTransport($transport);
        $orderData = $this->orderProcessor->process(
            $orderInput,
            $orderData,
            $this->domain->getCurrentDomainConfig(),
            $currentCustomerUser,
        );

        $calculatedTransportPrice = $orderData->totalPricesByItemType[OrderItem::TYPE_TRANSPORT];

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
