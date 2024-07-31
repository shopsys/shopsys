<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderRepository
{
    protected EntityManagerInterface $em;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createOrderQueryBuilder()
    {
        return $this->em->createQueryBuilder()
            ->select('o')
            ->from(Order::class, 'o')
            ->where('o.deleted = FALSE');
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    protected function findByUuidAndCustomerUser(string $uuid, CustomerUser $customerUser)
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.uuid = :uuid')->setParameter(':uuid', $uuid)
            ->andWhere('o.customerUser = :customerUser')->setParameter(':customerUser', $customerUser)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $uuid
     * @param string $urlHash
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    protected function findByUuidAndUrlHash(string $uuid, string $urlHash)
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.uuid = :uuid')->setParameter(':uuid', $uuid)
            ->andWhere('o.urlHash = :urlHash')->setParameter(':urlHash', $urlHash)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuidAndCustomerUser(string $uuid, CustomerUser $customerUser): Order
    {
        $order = $this->findByUuidAndCustomerUser($uuid, $customerUser);

        if ($order === null) {
            throw new OrderNotFoundUserError(sprintf(
                'Order with UUID \'%s\' not found.',
                $uuid,
            ));
        }

        return $order;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderLimitedList(CustomerUser $customerUser, int $limit, int $offset): array
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customerUser = :customerUser')
            ->setParameter('customerUser', $customerUser)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return int
     */
    public function getCustomerUserOrderCount(CustomerUser $customerUser): int
    {
        return $this->em->createQueryBuilder()
            ->select('count(o.id)')
            ->from(Order::class, 'o')
            ->where('o.deleted = FALSE')
            ->andWhere('o.customerUser = :customerUser')
            ->setParameter('customerUser', $customerUser)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        $order = $this->findByOrderNumberAndCustomerUser($orderNumber, $customerUser);

        if ($order === null) {
            throw new OrderNotFoundUserError(sprintf(
                'Order with order number \'%s\' not found.',
                $orderNumber,
            ));
        }

        return $order;
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    protected function findByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): ?Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.number = :orderNumber')->setParameter(':orderNumber', $orderNumber)
            ->andWhere('o.customerUser = :customerUser')->setParameter(':customerUser', $customerUser)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerOrderLimitedList(Customer $customer, int $limit, int $offset): array
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.customer = :customer')
            ->setParameter('customer', $customer)
            ->orderBy('o.createdAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return int
     */
    public function getCustomerOrderCount(Customer $customer): int
    {
        return $this->createOrderQueryBuilder()
            ->select('count(o.id)')
            ->andWhere('o.customer = :customerUser')
            ->setParameter('customerUser', $customer)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    protected function findByUuidAndCustomer(string $uuid, Customer $customer): ?Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.uuid = :uuid')->setParameter(':uuid', $uuid)
            ->andWhere('o.customer = :customer')->setParameter(':customer', $customer)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuidAndCustomer(string $uuid, Customer $customer): Order
    {
        $order = $this->findByUuidAndCustomer($uuid, $customer);

        if ($order === null) {
            throw new OrderNotFoundUserError(sprintf('Order with UUID \'%s\' not found.', $uuid));
        }

        return $order;
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    protected function findByOrderNumberAndCustomer(string $orderNumber, Customer $customer): ?Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.number = :number')->setParameter(':number', $orderNumber)
            ->andWhere('o.customer = :customer')->setParameter(':customer', $customer)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndCustomer(string $orderNumber, Customer $customer): Order
    {
        $order = $this->findByOrderNumberAndCustomer($orderNumber, $customer);

        if ($order === null) {
            throw new OrderNotFoundUserError(sprintf('Order with order number \'%s\' not found.', $orderNumber));
        }

        return $order;
    }
}
