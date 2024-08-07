<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;

class OrderFilterFactory
{
    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Shopsys\FrontendApiBundle\Model\Order\OrderFilter
     */
    public function createFromArgument(Argument $argument): OrderFilter
    {
        if (!isset($argument['filter'])) {
            return new OrderFilter();
        }

        $filter = $argument['filter'];

        return new OrderFilter(
            $filter['createdAfter'] ?? null,
            $filter['status'] ?? null,
            $filter['orderItemsCatnum'] ?? null,
            $filter['orderItemsProductUuid'] ?? null,
        );
    }
}
