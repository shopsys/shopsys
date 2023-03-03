<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersQuery as BaseOrdersQuery;

/**
 * @property \App\FrontendApi\Model\Order\OrderFacade $orderFacade
 */
class OrdersQuery extends BaseOrdersQuery
{
    /**
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\FrontendApi\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(CurrentCustomerUser $currentCustomerUser, OrderFacade $orderFacade)
    {
        parent::__construct($currentCustomerUser, $orderFacade);
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
