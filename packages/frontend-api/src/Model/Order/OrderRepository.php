<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;

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
     * @internal This will be replaced by \Shopsys\FrameworkBundle\Model\Order::getOrderRepository() with visibility set to public
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
            throw new OrderNotFoundException(sprintf(
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
}
