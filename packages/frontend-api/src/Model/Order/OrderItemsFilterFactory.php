<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderItemsFilterFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade
     */
    public function __construct(
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

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

        $orderStatus = isset($filter['orderStatus']) ? $this->orderStatusFacade->getAllByType($filter['orderStatus']) : null;

        return new OrderItemsFilter(
            $filter['orderUuid'] ?? null,
            $filter['orderCreatedAfter'] ?? null,
            $orderStatus,
            $filter['catnum'] ?? null,
            $filter['productUuid'] ?? null,
            $filter['type'] ?? null,
        );
    }
}
