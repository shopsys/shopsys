<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;

class OrderItemApiFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param string[] $uuids
     * @return array<string, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem>
     */
    public function findMappedByUuid(array $uuids): array
    {
        return $this->createOrderItemQueryBuilder()
            ->andWhere('oi.uuid IN (:uuids)')->setParameter(':uuids', $uuids)
            ->indexBy('oi', 'oi.uuid')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createOrderItemQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('oi')
            ->from(OrderItem::class, 'oi');
    }
}
