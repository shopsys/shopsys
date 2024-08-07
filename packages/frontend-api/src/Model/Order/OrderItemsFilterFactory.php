<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;

class OrderItemsFilterFactory
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter
     */
    public function createFromArgument(Argument $argument): OrderItemsFilter
    {
        if (!isset($argument['filter'])) {
            return new OrderItemsFilter();
        }

        $filter = $argument['filter'];

        return new OrderItemsFilter(
            $filter['orderUuid'] ?? null,
            $filter['orderCreatedAfter'] ?? null,
            $filter['orderStatus'] ?? null,
            $filter['catnum'] ?? null,
            $filter['productUuid'] ?? null,
            $filter['type'] ?? null,
        );
    }
}
