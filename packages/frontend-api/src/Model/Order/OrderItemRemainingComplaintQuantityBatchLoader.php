<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class OrderItemRemainingComplaintQuantityBatchLoader
{
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly OrderItemApiFacade $orderItemApiFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[] $orderItems
     */
    public function loadByOrderItems(array $orderItems): Promise
    {
        $remainingQuantities = $this->orderItemApiFacade->getRemainingComplaintQuantities($orderItems);

        return $this->promiseAdapter->all(array_map(
            static fn (OrderItem $orderItem): int => $remainingQuantities[$orderItem->getUuid()],
            $orderItems,
        ));
    }
}
