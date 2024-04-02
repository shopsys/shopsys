<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PlaceOrderFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitResolver
     */
    public function __construct(
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderStatusRepository $orderStatusRepository,
        protected readonly OrderPreviewFactory $orderPreviewFactory,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CustomerUserFacade $customerUserFacade,
        protected readonly PlacedOrderMessageDispatcher $placedOrderMessageDispatcher,
        protected readonly CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        protected readonly DeliveryAddressDataFactory $deliveryAddressDataFactory,
        protected readonly DeliveryAddressFactory $deliveryAddressFactory,
        protected readonly NewsletterFacade $newsletterFacade,
        protected readonly PromoCodeLimitResolver $promoCodeLimitResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderData $orderData
     * @param array $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null $promoCode
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function placeOrder(
        OrderData $orderData,
        array $quantifiedProducts,
        ?PromoCode $promoCode = null,
        ?DeliveryAddress $deliveryAddress = null,
    ): Order {
        $defaultOrderStatus = $this->orderStatusRepository->getDefault();
        $orderData->status = $defaultOrderStatus;
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        $orderPreview = $this->orderPreviewFactory->create(
            $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId()),
            $this->domain->getId(),
            $quantifiedProducts,
            $orderData->transport,
            $orderData->payment,
            $customerUser,
            $this->getPromoCodeDiscountPercent($quantifiedProducts, $promoCode),
            null,
            $promoCode,
        );

        $order = $this->orderFacade->createOrder($orderData, $orderPreview, $customerUser);

        if ($customerUser instanceof CustomerUser) {
            $customerUserUpdateData = $this->customerUserUpdateDataFactory->createFromCustomerUser($customerUser);
            $customerUserUpdateData->customerUserData->newsletterSubscription = $orderData->newsletterSubscription;
            $this->customerUserFacade->editByCustomerUser($customerUser->getId(), $customerUserUpdateData);
            $deliveryAddress = $deliveryAddress ?? $this->createDeliveryAddressForAmendingCustomerUserData($order);
            $this->customerUserFacade->amendCustomerUserDataFromOrder($customerUser, $order, $deliveryAddress);
        } elseif ($orderData->newsletterSubscription) {
            $newsletterSubscriber = $this->newsletterFacade->findNewsletterSubscriberByEmailAndDomainId(
                $orderData->email,
                $this->domain->getId(),
            );

            if ($newsletterSubscriber === null) {
                $this->newsletterFacade->addSubscribedEmail($orderData->email, $this->domain->getId());
            }
        }

        $this->placedOrderMessageDispatcher->dispatchPlacedOrderMessage($order->getId());

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return string|null
     */
    protected function getPromoCodeDiscountPercent(array $quantifiedProducts, ?PromoCode $promoCode): ?string
    {
        if ($promoCode === null) {
            return null;
        }
        $limit = $this->promoCodeLimitResolver->getLimitByPromoCode(
            $promoCode,
            $quantifiedProducts,
        );

        return $limit?->getDiscount();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null
     */
    protected function createDeliveryAddressForAmendingCustomerUserData(Order $order): ?DeliveryAddress
    {
        $orderTransport = $order->getTransportItem()->getTransport();

        if (
            $orderTransport->isPersonalPickup() ||
            $orderTransport->isPacketery() ||
            $order->isDeliveryAddressSameAsBillingAddress()
        ) {
            return null;
        }

        $deliveryAddressData = $this->deliveryAddressDataFactory->create();
        $deliveryAddressData->firstName = $order->getDeliveryFirstName();
        $deliveryAddressData->lastName = $order->getDeliveryLastName();
        $deliveryAddressData->companyName = $order->getDeliveryCompanyName();
        $deliveryAddressData->street = $order->getDeliveryStreet();
        $deliveryAddressData->city = $order->getDeliveryCity();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->country = $order->getDeliveryCountry();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->customer = $order->getCustomerUser()?->getCustomer();

        return $this->deliveryAddressFactory->create($deliveryAddressData);
    }
}
