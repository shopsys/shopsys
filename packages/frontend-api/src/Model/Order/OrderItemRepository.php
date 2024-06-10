<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class OrderItemRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order[] $orders
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[][]
     */
    public function loadAllByOrders(array $orders): array
    {
        $allOrderItemsByOrderId = [];

        foreach ($orders as $order) {
            $allOrderItemsByOrderId[$order->getId()] = [];
        }
        $orderItems = $this->entityManager->createQueryBuilder()
            ->select('oi')
            ->from(OrderItem::class, 'oi')
            ->where('oi.order IN (:orders)')
            ->setParameter('orders', $orders)
            ->orderBy('oi.id')
            ->getQuery()->execute();

        /** @var \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $allOrderItemsByOrderId[$orderItem->getOrder()->getId()][] = $orderItem;
        }

        return array_values($allOrderItemsByOrderId);
    }
}
