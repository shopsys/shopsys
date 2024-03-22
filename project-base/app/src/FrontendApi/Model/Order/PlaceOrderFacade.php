<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 */
class PlaceOrderFacade extends BasePlaceOrderFacade
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
        OrderFacade $orderFacade,
        OrderStatusRepository $orderStatusRepository,
        OrderPreviewFactory $orderPreviewFactory,
        CurrencyFacade $currencyFacade,
        Domain $domain,
        CurrentCustomerUser $currentCustomerUser,
        CustomerUserFacade $customerUserFacade,
        PlacedOrderMessageDispatcher $placedOrderMessageDispatcher,
        CustomerUserUpdateDataFactory $customerUserUpdateDataFactory,
        DeliveryAddressDataFactory $deliveryAddressDataFactory,
        DeliveryAddressFactory $deliveryAddressFactory,
        NewsletterFacade $newsletterFacade,
        protected readonly PromoCodeLimitResolver $promoCodeLimitResolver
    ) {
        parent::__construct($orderFacade, $orderStatusRepository, $orderPreviewFactory, $currencyFacade, $domain, $currentCustomerUser, $customerUserFacade, $placedOrderMessageDispatcher, $customerUserUpdateDataFactory, $deliveryAddressDataFactory, $deliveryAddressFactory, $newsletterFacade);
    }

    /**
     * @param \App\Model\Order\Order $order
     * @return \App\Model\Customer\DeliveryAddress|null
     */
    #[\Override]
    protected function createDeliveryAddressForAmendingCustomerUserData(Order $order): ?DeliveryAddress
    {
        if (
            $order->getTransport()->isPersonalPickup() ||
            $order->getTransport()->isPacketery() ||
            $order->isDeliveryAddressSameAsBillingAddress()
        ) {
            return null;
        }

        /** @var \App\Model\Customer\DeliveryAddress|null $deliveryAddress */
        $deliveryAddress = parent::createDeliveryAddressForAmendingCustomerUserData($order);

        return $deliveryAddress;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @param \App\Model\Order\PromoCode\PromoCode|null $promoCode
     * @return string|null
     */
    #[\Override]
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
}
