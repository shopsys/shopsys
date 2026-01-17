<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderApiFacade
{
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    public function getByUuid(string $orderUuid): Order
    {
        try {
            return $this->orderFacade->getByUuid($orderUuid);
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order with UUID \'' . $orderUuid . '\' not found.');
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
        OrderFilter $filter,
    ): array {
        return $this->orderRepository->getCustomerUserOrderLimitedList($customerUser, $limit, $offset, $filter);
    }

    public function getCustomerUserOrderCount(CustomerUser $customerUser, OrderFilter $filter): int
    {
        return $this->orderRepository->getCustomerUserOrderCount($customerUser, $filter);
    }

    public function getByUuidAndCustomerUser(string $uuid, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByUuidAndCustomerUser($uuid, $customerUser);
    }

    public function getByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    public function findLastOrderByCustomerUser(CustomerUser $customerUser): ?Order
    {
        $orderList = $this->orderRepository->getCustomerUserOrderLimitedList($customerUser, 1, 0);

        if ($orderList === []) {
            return null;
        }

        return $orderList[0];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerOrderLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
        OrderFilter $orderFilter,
    ): array {
        return $this->orderRepository->getCustomerOrderLimitedList($customer, $limit, $offset, $orderFilter);
    }

    public function getCustomerOrderCount(Customer $customer, OrderFilter $orderFilter): int
    {
        return $this->orderRepository->getCustomerOrderCount($customer, $orderFilter);
    }

    public function getByUuidAndCustomer(string $uuid, Customer $customer): Order
    {
        return $this->orderRepository->getByUuidAndCustomer($uuid, $customer);
    }

    public function getByOrderNumberAndCustomer(string $orderNumber, Customer $customer): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomer($orderNumber, $customer);
    }
}
