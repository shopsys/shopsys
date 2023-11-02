<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser as FrameworkCustomerUser;
use Shopsys\FrontendApiBundle\Model\Order\OrderRepository as BaseOrderRepository;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

/**
 * @method \App\Model\Order\Order|null findByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findByUuidAndUrlHash(string $uuid, string $urlHash)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class OrderRepository extends BaseOrderRepository
{
    /**
     * @param string $orderNumber
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Order\Order
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
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Order\Order|null
     */
    private function findByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): ?Order
    {
        return $this->createOrderQueryBuilder()
            ->andWhere('o.number = :orderNumber')->setParameter(':orderNumber', $orderNumber)
            ->andWhere('o.customerUser = :customerUser')->setParameter(':customerUser', $customerUser)
            ->getQuery()->getOneOrNullResult();
    }

    /**
     * @param string $uuid
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Order\Order
     */
    public function getByUuidAndCustomerUser(string $uuid, FrameworkCustomerUser $customerUser): Order
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
}
