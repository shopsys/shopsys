<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery as BaseOrdersQuery;

/**
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
 */
class OrdersQuery extends BaseOrdersQuery
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
     */
    public function __construct(
        CurrentCustomerUser $currentCustomerUser,
        OrderApiFacade $orderApiFacade,
    ) {
        parent::__construct($currentCustomerUser, $orderApiFacade);
    }

    /**
     * {@inheritdoc}
     */
    public function ordersQuery(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        return parent::ordersQuery($argument);
    }
}
