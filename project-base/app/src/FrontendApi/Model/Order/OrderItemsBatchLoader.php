<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class OrderItemsBatchLoader
{
    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\FrontendApi\Model\Order\OrderItemFacade $orderItemFacade
     */
    public function __construct(private PromiseAdapter $promiseAdapter, private OrderItemFacade $orderItemFacade)
    {
    }

    /**
     * @param \App\Model\Order\Order[] $orders
     * @return \GraphQL\Executor\Promise\Promise
     */
    public function loadAllByOrders(array $orders): Promise
    {
        return $this->promiseAdapter->all($this->orderItemFacade->loadAllByOrders($orders));
    }
}
