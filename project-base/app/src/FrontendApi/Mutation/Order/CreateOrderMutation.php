<?php

declare(strict_types=1);

namespace App\FrontendApi\Mutation\Order;

use Shopsys\FrontendApiBundle\Model\Mutation\Order\CreateOrderMutation as BaseCreateOrderMutation;

/**
 * @property \App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Order\PlaceOrderFacade $placeOrderFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderDataFactory $orderDataFactory, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrontendApiBundle\Model\Cart\CartApiFacade $cartApiFacade, \Shopsys\FrontendApiBundle\Model\Order\CreateOrderResultFactory $createOrderResultFactory, \Shopsys\FrontendApiBundle\Model\Cart\CartWatcherFacade $cartWatcherFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessor $orderProcessor, \App\Model\Order\PlaceOrderFacade $placeOrderFacade, \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory $orderInputFactory)
 * @method string[] computeValidationGroups(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Customer\User\CustomerUser|null $currentCustomerUser)
 * @method void validateCreateOrderMutation(\Overblog\GraphQLBundle\Definition\Argument $argument, \Overblog\GraphQLBundle\Validator\InputValidator $validator, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Cart\Cart getCartForCreateOrderMutation(array<string, mixed> $input, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult getCheckedCartWithModifications(\App\Model\Cart\Cart $cart)
 * @method \App\Model\Order\Order createOrderFromCart(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Cart\Cart $cart, string|null $deliveryAddressUuid)
 * @method \App\Model\Order\OrderData getProcessedOrderData(\Overblog\GraphQLBundle\Definition\Argument $argument, \App\Model\Cart\Cart $cart, string|null $deliveryAddressUuid)
 * @method \Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput createOrderInputFromCart(\App\Model\Cart\Cart $cart, string|null $deliveryAddressUuid)
 */
class CreateOrderMutation extends BaseCreateOrderMutation
{
}
