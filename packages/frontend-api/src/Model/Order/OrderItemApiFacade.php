<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\String\DatabaseSearchingHelper;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Withdrawal\WithdrawalRequest;
use Shopsys\FrameworkBundle\Model\Product\Product;

class OrderItemApiFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DatabaseSearchingHelper $databaseSearchingHelper,
    ) {
    }

    /**
     * @param string[] $uuids
     * @return array<string, \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem>
     */
    public function findMappedByUuid(array $uuids): array
    {
        return $this->createOrderItemExcludingOrdersWithWithdrawalQueryBuilder()
            ->andWhere('oi.uuid IN (:uuids)')->setParameter(':uuids', $uuids)
            ->indexBy('oi', 'oi.uuid')
            ->getQuery()
            ->getResult();
    }

    /**
     * The purchase belongs to the whole customer, so any of its users can be verified by it,
     * no matter who of them placed the order
     */
    public function findNewestReviewableOrderItemByCustomer(
        Customer $customer,
        Product $product,
        int $domainId,
    ): ?OrderItem {
        return $this->createNewestReviewableOrderItemQueryBuilder($product, $domainId)
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * The url hash authorizes an unregistered customer the same way it does for the order detail page
     */
    public function findNewestReviewableOrderItemByOrderUrlHash(
        string $orderUrlHash,
        Product $product,
        int $domainId,
    ): ?OrderItem {
        return $this->createNewestReviewableOrderItemQueryBuilder($product, $domainId)
            ->andWhere('o.urlHash = :orderUrlHash')
            ->setParameter('orderUrlHash', $orderUrlHash)
            ->getQuery()
            ->getOneOrNullResult();
    }

    protected function createNewestReviewableOrderItemQueryBuilder(Product $product, int $domainId): QueryBuilder
    {
        return $this->createProductOrderItemOnDomainQueryBuilder($domainId)
            ->join('o.status', 'os')
            ->andWhere('os.productReviewsAllowed = TRUE')
            ->andWhere('oi.product = :product')
            ->setParameter('product', $product)
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('oi.id', 'DESC')
            ->setMaxResults(1);
    }

    protected function createProductOrderItemOnDomainQueryBuilder(int $domainId): QueryBuilder
    {
        return $this->createOrderItemExcludingOrdersWithWithdrawalQueryBuilder()
            ->andWhere('o.deleted = FALSE')
            ->andWhere('o.domainId = :domainId')
            ->andWhere('oi.type = :productItemType')
            ->setParameter('domainId', $domainId)
            ->setParameter('productItemType', OrderItemTypeEnum::TYPE_PRODUCT);
    }

    protected function createOrderItemExcludingOrdersWithWithdrawalQueryBuilder(): QueryBuilder
    {
        return $this->em->createQueryBuilder()
            ->select('oi')
            ->from(OrderItem::class, 'oi')
            ->join('oi.order', 'o')
            ->andWhere('NOT EXISTS(
               SELECT 1 FROM ' . WithdrawalRequest::class . ' wr
               WHERE wr.order = o
            )');
    }

    /**
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

    protected function createCustomerUserOrderItemsLimitedSearchListQueryBuilder(
        CustomerUser $customerUser,
        string $search,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createCustomerUserOrderItemsLimitedListQueryBuilder($customerUser, $filter);

        return $this->applySearchToQueryBuilder($queryBuilder, $search);
    }

    protected function createCustomerOrderItemsLimitedSearchListQueryBuilder(
        Customer $customer,
        string $search,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createCustomerOrderItemsLimitedListQueryBuilder($customer, $filter);

        $this->applySearchToQueryBuilder($queryBuilder, $search);

        return $queryBuilder;
    }

    protected function createCustomerUserOrderItemsLimitedListQueryBuilder(
        CustomerUser $customerUser,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createOrderItemExcludingOrdersWithWithdrawalQueryBuilder()
            ->andWhere('o.customerUser = :customerUser')
            ->setParameter(':customerUser', $customerUser);

        $this->applyOrderItemsFilterToQueryBuilder($filter, $queryBuilder);

        return $queryBuilder;
    }

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
            $queryBuilder->andWhere(array_first($orX));
        }
    }

    protected function createCustomerOrderItemsLimitedListQueryBuilder(
        Customer $customer,
        OrderItemsFilter $filter,
    ): QueryBuilder {
        $queryBuilder = $this->createOrderItemExcludingOrdersWithWithdrawalQueryBuilder()
            ->andWhere('o.customer = :customer')
            ->setParameter(':customer', $customer);

        $this->applyOrderItemsFilterToQueryBuilder($filter, $queryBuilder);

        return $queryBuilder;
    }

    protected function applySearchToQueryBuilder(QueryBuilder $queryBuilder, string $search): QueryBuilder
    {
        $queryBuilder->andWhere(
            $this->em->getExpressionBuilder()->orX(
                'NORMALIZED(oi.name) LIKE NORMALIZED(:search)',
                'NORMALIZED(oi.catnum) LIKE NORMALIZED(:search)',
                'NORMALIZED(o.number) LIKE NORMALIZED(:search)',
            ),
        )->setParameter(':search', $this->databaseSearchingHelper->getFullTextLikeSearchString($search));

        return $queryBuilder;
    }
}
