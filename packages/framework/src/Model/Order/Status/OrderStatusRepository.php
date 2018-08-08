<?php

namespace Shopsys\FrameworkBundle\Model\Order\Status;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Order;

class OrderStatusRepository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    protected function getOrderStatusRepository(): \Doctrine\ORM\EntityRepository
    {
        return $this->em->getRepository(OrderStatus::class);
    }
    
    public function findById(int $orderStatusId): ?\Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        return $this->getOrderStatusRepository()->find($orderStatusId);
    }
    
    public function getById(int $orderStatusId): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        $orderStatus = $this->findById($orderStatusId);

        if ($orderStatus === null) {
            $message = 'Order status with ID ' . $orderStatusId . ' not found.';
            throw new \Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusNotFoundException($message);
        }

        return $orderStatus;
    }

    public function getDefault(): \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
    {
        $orderStatus = $this->getOrderStatusRepository()->findOneBy(['type' => OrderStatus::TYPE_NEW]);

        if ($orderStatus === null) {
            $message = 'Default order status not found.';
            throw new \Shopsys\FrameworkBundle\Model\Order\Status\Exception\OrderStatusNotFoundException($message);
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
    public function getAllIndexedById(): array
    {
        $orderStatusesIndexedById = [];

        foreach ($this->getAll() as $orderStatus) {
            $orderStatusesIndexedById[$orderStatus->getId()] = $orderStatus;
        }

        return $orderStatusesIndexedById;
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
