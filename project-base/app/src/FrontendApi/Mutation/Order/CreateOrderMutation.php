<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation as BaseCreateOrderMutation;

/**
 * @property \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @method sendEmail(\App\Model\Order\Order $order)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory, \App\Model\Order\Mail\OrderMailFacade $orderMailFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade, \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade, \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory, \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor, \App\Model\Order\PlaceOrderFacade $placeOrderFacade, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory, \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory, \App\Model\Customer\User\CustomerUserFacade $customerUserFacade, \Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade $newsletterFacade, \Shopsys\FrameworkBundle\Model\Order\Messenger\PlacedOrderMessageDispatcher $placedOrderMessageDispatcher, \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressFactory $deliveryAddressFactory, \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory)
 * @method \App\Model\Customer\DeliveryAddress|null resolveDeliveryAddress(string|null $deliveryAddressUuid, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @property \App\Model\Customer\User\CustomerUserUpdateDataFactory $customerUserUpdateDataFactory
 * @property \App\Model\Customer\User\CustomerUserFacade $customerUserFacade
 * @property \App\Model\Customer\DeliveryAddressDataFactory $deliveryAddressDataFactory
 * @method \App\Model\Customer\DeliveryAddress|null createDeliveryAddressForAmendingCustomerUserData(\App\Model\Order\Order $order)
 * @property \App\Model\Order\PlaceOrderFacade $placeOrderFacade
 */
class CreateOrderMutation extends BaseCreateOrderMutation
{
}
