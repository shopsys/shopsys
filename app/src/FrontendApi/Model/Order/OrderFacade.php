<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade as BaseOrderFacade;

/**
 * @property \App\FrontendApi\Model\Order\OrderRepository $orderRepository
 * @method __construct(\App\FrontendApi\Model\Order\OrderRepository $orderRepository)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 */
class OrderFacade extends BaseOrderFacade
{
    /**
     * @param string $orderNumber
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Order\Order
     */
    public function getByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    /**
     * @param string $uuid
     * @return \App\Model\Order\Order
     */
    public function getByUuid(string $uuid): Order
    {
        return $this->orderRepository->getByUuid($uuid);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @return \App\Model\Order\Order|null
     */
    public function findLastOrderByCustomerUser(CustomerUser $customerUser): ?Order
    {
        $orderList = $this->orderRepository->getCustomerUserOrderLimitedList($customerUser, 1, 0);

        if ($orderList === []) {
            return null;
        }

        return $orderList[0];
    }
}
