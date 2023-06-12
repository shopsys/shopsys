<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Order\Item\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class OrderItemRepository
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @param \App\Model\Order\Order[] $orders
     * @return \App\Model\Order\Item\OrderItem[][]
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

        /** @var \App\Model\Order\Item\OrderItem $orderItem */
        foreach ($orderItems as $orderItem) {
            $allOrderItemsByOrderId[$orderItem->getOrder()->getId()][] = $orderItem;
        }

        return array_values($allOrderItemsByOrderId);
    }
}
