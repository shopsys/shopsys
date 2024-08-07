<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery as BaseOrdersQuery;

/**
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade, \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade, \Symfony\Component\Security\Core\Security $security, \Shopsys\FrontendApiBundle\Model\Order\OrderFilterFactory $orderFilterFactory)
 * @method \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise getPaginatedCustomerUserOrders(\App\Model\Customer\User\CustomerUser $customerUser, \Overblog\GraphQLBundle\Definition\Argument $argument)
 * @method bool canSeeAllOrdersFromCustomer(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class OrdersQuery extends BaseOrdersQuery
{
}
