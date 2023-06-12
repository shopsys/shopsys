<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

class OrderItemFacade
{
    /**
     * @param \App\FrontendApi\Model\Order\OrderItemRepository $orderItemRepository
     */
    public function __construct(private OrderItemRepository $orderItemRepository)
    {
    }

    /**
     * @param \App\Model\Order\Order[] $orders
     * @return \App\Model\Order\Item\OrderItem[][]
     */
    public function loadAllByOrders(array $orders): array
    {
        return $this->orderItemRepository->loadAllByOrders($orders);
    }
}
