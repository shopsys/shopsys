<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use GraphQL\Executor\Promise\Promise;
use GraphQL\Executor\Promise\PromiseAdapter;

class OrderItemsBatchLoader
{
    /**
     * @var \GraphQL\Executor\Promise\PromiseAdapter
     */
    private PromiseAdapter $promiseAdapter;

    /**
     * @var \App\FrontendApi\Model\Order\OrderItemFacade
     */
    private OrderItemFacade $orderItemFacade;

    /**
     * @param \GraphQL\Executor\Promise\PromiseAdapter $promiseAdapter
     * @param \App\FrontendApi\Model\Order\OrderItemFacade $orderItemFacade
     */
    public function __construct(PromiseAdapter $promiseAdapter, OrderItemFacade $orderItemFacade)
    {
        $this->promiseAdapter = $promiseAdapter;
        $this->orderItemFacade = $orderItemFacade;
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
