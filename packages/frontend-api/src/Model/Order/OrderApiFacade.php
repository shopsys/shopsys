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
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     */
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderFacade $orderFacade,
    ) {
    }

    /**
     * @param string $orderUuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuid(string $orderUuid): Order
    {
        try {
            return $this->orderFacade->getByUuid($orderUuid);
        } catch (OrderNotFoundException) {
            throw new OrderNotFoundUserError('Order with UUID \'' . $orderUuid . '\' not found.');
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter
     * @return int
     */
    public function getCustomerUserOrderCount(CustomerUser $customerUser, OrderFilter $filter): int
    {
        return $this->orderRepository->getCustomerUserOrderCount($customerUser, $filter);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuidAndCustomerUser(string $uuid, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByUuidAndCustomerUser($uuid, $customerUser);
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order|null
     */
    public function findLastOrderByCustomerUser(CustomerUser $customerUser): ?Order
    {
        $orderList = $this->orderRepository->getCustomerUserOrderLimitedList($customerUser, 1, 0);

        if ($orderList === []) {
            return null;
        }

        return $orderList[0];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $orderFilter
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $orderFilter
     * @return int
     */
    public function getCustomerOrderCount(Customer $customer, OrderFilter $orderFilter): int
    {
        return $this->orderRepository->getCustomerOrderCount($customer, $orderFilter);
    }

    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByUuidAndCustomer(string $uuid, Customer $customer): Order
    {
        return $this->orderRepository->getByUuidAndCustomer($uuid, $customer);
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function getByOrderNumberAndCustomer(string $orderNumber, Customer $customer): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomer($orderNumber, $customer);
    }
}
