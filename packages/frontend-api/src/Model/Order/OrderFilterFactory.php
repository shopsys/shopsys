<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Overblog\GraphQLBundle\Definition\Argument;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;

class OrderFilterFactory
{
    public function __construct(
        protected readonly OrderStatusFacade $orderStatusFacade,
    ) {
    }

    public function createFromArgument(Argument $argument): OrderFilter
    {
        if (!isset($argument['filter'])) {
            return new OrderFilter();
        }

        $filter = $argument['filter'];

        $statusCodes = $filter['statusCodes'] ?? [];

        $orderStatuses = $this->orderStatusFacade->getAllByCodes($statusCodes);

        $search = isset($filter['search']) ? trim($filter['search']) : null;

        return new OrderFilter(
            $filter['createdAfter'] ?? null,
            $filter['createdBefore'] ?? null,
            $orderStatuses !== [] ? $orderStatuses : null,
            $filter['orderItemsCatnum'] ?? null,
            $filter['orderItemsProductUuid'] ?? null,
            $search !== '' ? $search : null,
        );
    }
}
