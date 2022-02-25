<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersResolver as BaseOrdersResolver;

/**
 * @property \App\FrontendApi\Model\Order\OrderFacade $orderFacade
 * @method __construct(\Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\FrontendApi\Model\Order\OrderFacade $orderFacade)
 */
class OrdersResolver extends BaseOrdersResolver
{
    /**
     * {@inheritdoc}
     */
    public function resolve(Argument $argument)
    {
        PageSizeValidator::checkMaxPageSize($argument);

        return parent::resolve($argument);
    }
}
