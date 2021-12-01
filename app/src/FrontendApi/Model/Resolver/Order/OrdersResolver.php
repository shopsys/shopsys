<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Resolver\Order;

use App\FrontendApi\Component\Validation\PageSizeValidator;
use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrdersResolver as BaseOrdersResolver;

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
