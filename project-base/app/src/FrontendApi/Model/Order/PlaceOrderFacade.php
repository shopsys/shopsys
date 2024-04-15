<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\PlaceOrderFacade as BasePlaceOrderFacade;

/**
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
 * @method __construct(\App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository $orderStatusRepository, \Shopsys\FrameworkBundle\Model\Order\Preview\OrderPreviewFactory $orderPreviewFactory, \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade, \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimitResolver $promoCodeLimitResolver)
 * @method \App\Model\Order\Order placeOrder(\App\Model\Order\OrderData $orderData, array $quantifiedProducts, \App\Model\Order\PromoCode\PromoCode|null $promoCode = null, \App\Model\Customer\DeliveryAddress|null $deliveryAddress = null)
 * @method string|null getPromoCodeDiscountPercent(\Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts, \App\Model\Order\PromoCode\PromoCode|null $promoCode)
 * @method \App\Model\Customer\DeliveryAddress|null createDeliveryAddressForAmendingCustomerUserData(\App\Model\Order\Order $order)
 * @method setOrderDataDeliveryFieldsByDeliveryAddress(\App\Model\Customer\DeliveryAddress $deliveryAddress, \App\Model\Order\OrderData $orderData)
 */
class PlaceOrderFacade extends BasePlaceOrderFacade
{
}
