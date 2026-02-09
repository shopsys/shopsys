<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusNotFoundException;

class OrderStatusRepository
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getOrderStatusRepository(): EntityRepository
    {
        return $this->em->getRepository(OrderStatus::class);
    }

    public function findById(int $orderStatusId): ?OrderStatus
    {
        return $this->getOrderStatusRepository()->find($orderStatusId);
    }

    public function getById(int $orderStatusId): OrderStatus
    {
        $orderStatus = $this->findById($orderStatusId);

        if ($orderStatus === null) {
            $message = 'Order status with ID ' . $orderStatusId . ' not found.';

            throw new OrderStatusNotFoundException($message);
        }

        return $orderStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllByType(string $type): array
    {
        return $this->getOrderStatusRepository()->findBy(['type' => $type]);
    }

    public function getByType(string $statusType): OrderStatus
    {
        $orderStatus = $this->getOrderStatusRepository()->findOneBy(['type' => $statusType]);

        if ($orderStatus === null) {
            $message = 'Order status with type ' . $statusType . ' not found.';

            throw new OrderStatusNotFoundException($message);
        }

        return $orderStatus;
    }

    public function getDefault(): OrderStatus
    {
        $orderStatus = $this->getOrderStatusRepository()->findOneBy(['type' => OrderStatusTypeEnum::TYPE_NEW]);

        if ($orderStatus === null) {
            $message = 'Default order status not found.';

            throw new OrderStatusNotFoundException($message);
        }

        return $orderStatus;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAll(): array
    {
        return $this->getOrderStatusRepository()->findBy([], ['id' => 'asc']);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus[]
     */
    public function getAllExceptId(int $orderStatusId): array
    {
        $qb = $this->getOrderStatusRepository()->createQueryBuilder('os')
            ->where('os.id != :id')
            ->setParameter('id', $orderStatusId);

        return $qb->getQuery()->getResult();
    }

    public function replaceOrderStatus(OrderStatus $oldOrderStatus, OrderStatus $newOrderStatus): void
    {
        $this->em->createQueryBuilder()
            ->update(Order::class, 'o')
            ->set('o.status', ':newOrderStatus')->setParameter('newOrderStatus', $newOrderStatus)
            ->where('o.status = :oldOrderStatus')->setParameter('oldOrderStatus', $oldOrderStatus)
            ->getQuery()->execute();
    }
}
