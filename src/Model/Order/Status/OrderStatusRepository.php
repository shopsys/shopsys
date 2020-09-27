<?php

declare(strict_types=1);

namespace App\Model\Order\Status;

use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusRepository as BaseOrderStatusRepository;

/**
 * @method \App\Model\Order\Status\OrderStatus|null findById(int $orderStatusId)
 * @method \App\Model\Order\Status\OrderStatus getById(int $orderStatusId)
 * @method \App\Model\Order\Status\OrderStatus getDefault()
 * @method \App\Model\Order\Status\OrderStatus[] getAll()
 * @method \App\Model\Order\Status\OrderStatus[] getAllIndexedById()
 * @method \App\Model\Order\Status\OrderStatus[] getAllExceptId(int $orderStatusId)
 * @method replaceOrderStatus(\App\Model\Order\Status\OrderStatus $oldOrderStatus, \App\Model\Order\Status\OrderStatus $newOrderStatus)
 */
class OrderStatusRepository extends BaseOrderStatusRepository
{
    public function findByType(int $type): ?OrderStatus
    {
        /** @var OrderStatus|null $orderStatus */
        $orderStatus = $this->getOrderStatusRepository()->findOneBy([
            'type' => $type
        ]);

        return $orderStatus;
    }
}
