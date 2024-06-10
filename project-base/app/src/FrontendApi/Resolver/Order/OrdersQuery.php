<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery as BaseOrdersQuery;

/**
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade)
 */
class OrdersQuery extends BaseOrdersQuery
{
    /**
     * {@inheritdoc}
     */
    public function ordersQuery(Argument $argument): ConnectionInterface|Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        return parent::ordersQuery($argument);
    }
}
