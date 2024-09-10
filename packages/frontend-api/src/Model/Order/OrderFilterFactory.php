<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderFilterFactory
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
     * @return \Shopsys\FrontendApiBundle\Model\Order\OrderFilter
     */
    public function createFromArgument(Argument $argument): OrderFilter
    {
        if (!isset($argument['filter'])) {
            return new OrderFilter();
        }

        $filter = $argument['filter'];

        $status = isset($filter['status']) ? $this->orderStatusFacade->getAllByType($filter['status']) : null;

        return new OrderFilter(
            $filter['createdAfter'] ?? null,
            $status,
            $filter['orderItemsCatnum'] ?? null,
            $filter['orderItemsProductUuid'] ?? null,
        );
    }
}
