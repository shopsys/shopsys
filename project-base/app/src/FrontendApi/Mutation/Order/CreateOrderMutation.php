<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation as BaseCreateOrderMutation;

/**
 * @property \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade
 * @property \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Order\Mail\OrderMailFacade $orderMailFacade
 * @method sendEmail(\App\Model\Order\Order $order)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory, \App\FrontendApi\Model\Order\PlaceOrderFacade $placeOrderFacade, \App\Model\Order\Mail\OrderMailFacade $orderMailFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade, \App\Model\Customer\DeliveryAddressFacade $deliveryAddressFacade, \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory, \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade)
 * @method \App\Model\Customer\DeliveryAddress|null resolveDeliveryAddress(string|null $deliveryAddressUuid, \App\Model\Customer\User\CustomerUser|null $customerUser)
 */
class CreateOrderMutation extends BaseCreateOrderMutation
{
}
