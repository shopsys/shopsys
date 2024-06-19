<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

class OrderItemFacade
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemRepository $orderItemRepository
     */
    public function __construct(
        protected readonly OrderItemRepository $orderItemRepository,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order[] $orders
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[][]
     */
    public function loadAllByOrders(array $orders): array
    {
        return $this->orderItemRepository->loadAllByOrders($orders);
    }
}
