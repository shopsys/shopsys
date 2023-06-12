<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Customer\DeliveryAddress;
use App\Model\Customer\DeliveryAddressDataFactory;
use App\Model\Customer\User\CustomerUserUpdateDataFactory;
use App\Model\Order\PromoCode\PromoCode;
use App\Model\Order\PromoCode\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @method \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreview createOrderPreview(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Transport\Transport|null $transport, \App\Model\Payment\Payment|null $payment, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 */
class PlaceOrderFacade extends BasePlaceOrderFacade
{
    /**
     * @param \App\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderProductFacade $orderProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository
     * @param \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade $customerUserFacade
     * @param \App\Model\Order\PromoCode\PromoCodeLimitResolver $promoCodeLimitResolver
     * @param \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory
     * @param \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade
     */
    public function __construct(
        OrderFacade $orderFacade,
        OrderProductFacade $orderProductFacade,
        OrderStatusRepository $orderStatusRepository,
        OrderPreviewFactory $orderPreviewFactory,
        CurrencyFacade $currencyFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CustomerUserFacade $customerUserFacade,
        private PromoCodeLimitResolver $promoCodeLimitResolver,
        private DeliveryAddressDataFactory $deliveryAddressDataFactory,
        private DeliveryAddressFactory $deliveryAddressFactory,
        private CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        private NewsletterFacade $newsletterFacade,
    ) {
        parent::__construct(
            $orderFacade,
            $orderProductFacade,
            $orderStatusRepository,
            $orderPreviewFactory,
            $currencyFacade,
            $domain,
            $currentCustomerUser,
            $customerUserFacade,
        );
    }

    /**
     * @param \App\Model\Order\OrderData $orderData
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @param \App\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \App\Model\Order\Order
     */
    public function placeOrder(
        OrderData $orderData,
        array $quantifiedProducts,
        ?PromoCode $promoCode = null,
        ?DeliveryAddress $deliveryAddress = null,
    ): Order {
        /** @var \App\Model\Order\Status\OrderStatus $defaultOrderStatus */
        $defaultOrderStatus = $this->orderStatusRepository->getDefault();
        $orderData->status = $defaultOrderStatus;
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
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
        $this->orderProductFacade->subtractOrderProductsFromStock($order->getProductItems());

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

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return string|null
     */
    private function getPromoCodeDiscountPercent(array $quantifiedProducts, ?PromoCode $promoCode): ?string
    {
        if ($promoCode === null) {
            return null;
        }
        $limit = $this->promoCodeLimitResolver->getLimitByPromoCode(
            $promoCode,
            $quantifiedProducts,
        );

        return $limit !== null ? $limit->getDiscount() : null;
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Customer\DeliveryAddress|null
     */
    private function createDeliveryAddressForAmendingCustomerUserData(Order $order): ?DeliveryAddress
    {
        if (
            $order->getTransport()->isPersonalPickup() ||
            $order->getTransport()->isPacketery() ||
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
        $deliveryAddressData->customer = $order->getCustomerUser()->getCustomer();

        /** @var \App\Model\Customer\DeliveryAddress $deliveryAddress */
        $deliveryAddress = $this->deliveryAddressFactory->create($deliveryAddressData);

        return $deliveryAddress;
    }
}
