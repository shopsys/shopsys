<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class OrderItemsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemFacade $orderItemFacade
     */
    public function __construct(
        protected readonly PromiseAdapter $promiseAdapter,
        protected readonly OrderItemFacade $orderItemFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order[] $orders
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadAllByOrders(array $orders): Promise
    {
        return $this->promiseAdapter->all($this->orderItemFacade->loadAllByOrders($orders));
    }
}
