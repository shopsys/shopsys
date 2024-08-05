<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
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

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getCustomerUserOrderItemsLimitedSearchList(
        string $search,
        CustomerUser $customerUser,
        int $limit,
        int $offset,
    ): array {
        return $this->createCustomerUserOrderItemsLimitedSearchListQueryBuilder($customerUser, $search)
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return int
     */
    public function getCustomerUserOrderItemsLimitedSearchListCount(
        string $search,
        CustomerUser $customerUser,
    ): int {
        return $this->createCustomerUserOrderItemsLimitedSearchListQueryBuilder($customerUser, $search)
            ->select('count(oi.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerUserOrderItemsLimitedSearchListQueryBuilder(
        CustomerUser $customerUser,
        string $search,
    ): QueryBuilder {
        return $this->createOrderItemQueryBuilder()
            ->join('oi.order', 'o')
            ->andWhere('o.customerUser = :customerUser')->setParameter(':customerUser', $customerUser)
            ->andWhere(
                $this->em->getExpressionBuilder()->orX(
                    'NORMALIZED(oi.name) LIKE NORMALIZED(:search)',
                    'NORMALIZED(oi.catnum) LIKE NORMALIZED(:search)',
                    'NORMALIZED(o.number) LIKE NORMALIZED(:search)',
                ),
            )
            ->setParameter(':search', DatabaseSearching::getFullTextLikeSearchString($search));
    }
}
