<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearching;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getCustomerUserOrderItemsLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        OrderItemsFilter $filter,
    ): array {
        return $this->createCustomerUserOrderItemsLimitedListQueryBuilder($customerUser, $filter)
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('oi.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return int
     */
    public function getCustomerUserOrderItemsLimitedListCount(
        CustomerUser $customerUser,
        OrderItemsFilter $filter,
    ): int {
        return $this->createCustomerUserOrderItemsLimitedListQueryBuilder($customerUser, $filter)
            ->select('count(oi.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getCustomerOrderItemsLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
        OrderItemsFilter $filter,
    ): array {
        $queryBuilder = $this->createCustomerOrderItemsLimitedListQueryBuilder($customer, $filter);

        return $queryBuilder
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('oi.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return int
     */
    public function getCustomerOrderItemsLimitedListCount(
        Customer $customer,
        OrderItemsFilter $filter,
    ): int {
        return $this->createCustomerOrderItemsLimitedListQueryBuilder($customer, $filter)
            ->select('count(oi.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getCustomerUserOrderItemsLimitedSearchList(
        string $search,
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        OrderItemsFilter $filter,
    ): array {
        return $this->createCustomerUserOrderItemsLimitedSearchListQueryBuilder($customerUser, $search, $filter)
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('oi.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return int
     */
    public function getCustomerUserOrderItemsLimitedSearchListCount(
        string $search,
        CustomerUser $customerUser,
        OrderItemsFilter $filter,
    ): int {
        return $this->createCustomerUserOrderItemsLimitedSearchListQueryBuilder($customerUser, $search, $filter)
            ->select('count(oi.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getCustomerOrderItemsLimitedSearchList(
        string $search,
        Customer $customer,
        int $limit,
        int $offset,
        OrderItemsFilter $filter,
    ): array {
        return $this->createCustomerOrderItemsLimitedSearchListQueryBuilder($customer, $search, $filter)
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('oi.id', 'DESC')
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $search
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return int
     */
    public function getCustomerOrderItemsLimitedSearchListCount(
        string $search,
        Customer $customer,
        OrderItemsFilter $filter,
    ): int {
        return $this->createCustomerOrderItemsLimitedSearchListQueryBuilder($customer, $search, $filter)
            ->select('count(oi.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $search
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerUserOrderItemsLimitedSearchListQueryBuilder(
        CustomerUser $customerUser,
        string $search,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createCustomerUserOrderItemsLimitedListQueryBuilder($customerUser, $filter);

        return $this->applySearchToQueryBuilder($queryBuilder, $search);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param string $search
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerOrderItemsLimitedSearchListQueryBuilder(
        Customer $customer,
        string $search,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createCustomerOrderItemsLimitedListQueryBuilder($customer, $filter);

        $this->applySearchToQueryBuilder($queryBuilder, $search);

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerUserOrderItemsLimitedListQueryBuilder(
        CustomerUser $customerUser,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createOrderItemQueryBuilder()
            ->join('oi.order', 'o')
            ->andWhere('o.customerUser = :customerUser')
            ->setParameter(':customerUser', $customerUser);

        $this->applyOrderItemsFilterToQueryBuilder($filter, $queryBuilder);

        return $queryBuilder;
    }

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    protected function applyOrderItemsFilterToQueryBuilder(OrderItemsFilter $filter, QueryBuilder $queryBuilder): void
    {
        if ($filter->getOrderUuid() !== null) {
            $queryBuilder->andWhere('o.uuid = :orderUuid')
                ->setParameter(':orderUuid', $filter->getOrderUuid());
        }

        if ($filter->getOrderCreatedAfter() !== null) {
            $queryBuilder->andWhere('o.createdAt >= :orderCreatedAfter')
                ->setParameter(':orderCreatedAfter', $filter->getOrderCreatedAfter());
        }

        if ($filter->getOrderStatuses() !== null && count($filter->getOrderStatuses()) > 0) {
            $queryBuilder->andWhere('o.status IN (:orderStatuses)')
                ->setParameter(':orderStatuses', $filter->getOrderStatuses());
        }

        if ($filter->getType() !== null) {
            $queryBuilder->andWhere('oi.type = :type')
                ->setParameter(':type', $filter->getType());
        }

        $orX = [];

        if ($filter->getCatnum() !== null) {
            $orX[] = 'oi.catnum = :catnum';
            $queryBuilder->setParameter(':catnum', $filter->getCatnum());
        }

        if ($filter->getProductUuid() !== null) {
            $orX[] = 'p.uuid = :productUuid';
            $queryBuilder->leftJoin('oi.product', 'p')
                ->setParameter(':productUuid', $filter->getProductUuid());
        }

        if (count($orX) > 1) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(...$orX));
        } elseif (count($orX) > 0) {
            $queryBuilder->andWhere(reset($orX));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createCustomerOrderItemsLimitedListQueryBuilder(
        Customer $customer,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createOrderItemQueryBuilder()
            ->join('oi.order', 'o')
            ->andWhere('o.customer = :customer')
            ->setParameter(':customer', $customer);

        $this->applyOrderItemsFilterToQueryBuilder($filter, $queryBuilder);

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function applySearchToQueryBuilder(QueryBuilder $queryBuilder, string $search): QueryBuilder
    {
        $queryBuilder->andWhere(
            $this->em->getExpressionBuilder()->orX(
                'NORMALIZED(oi.name) LIKE NORMALIZED(:search)',
                'NORMALIZED(oi.catnum) LIKE NORMALIZED(:search)',
                'NORMALIZED(o.number) LIKE NORMALIZED(:search)',
            ),
        )->setParameter(':search', DatabaseSearching::getFullTextLikeSearchString($search));

        return $queryBuilder;
    }
}
