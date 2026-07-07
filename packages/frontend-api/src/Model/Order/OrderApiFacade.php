<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderApiFacade
{
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderFacade $orderFacade,
        protected readonly OrderStatusFacade $orderStatusFacade,
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

    /**
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    public function getCustomerUserOrderStatusCounts(
        CustomerUser $customerUser,
        OrderFilter $filter,
        string $locale,
    ): array {
        return $this->getOrderStatusesWithCounts(
            $this->orderRepository->getCustomerUserOrderStatusCounts($customerUser, $filter),
            $locale,
        );
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

    /**
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    public function getCustomerOrderStatusCounts(
        Customer $customer,
        OrderFilter $orderFilter,
        string $locale,
    ): array {
        return $this->getOrderStatusesWithCounts(
            $this->orderRepository->getCustomerOrderStatusCounts($customer, $orderFilter),
            $locale,
        );
    }

    public function getByUuidAndCustomer(string $uuid, Customer $customer): Order
    {
        return $this->orderRepository->getByUuidAndCustomer($uuid, $customer);
    }

    public function getByOrderNumberAndCustomer(string $orderNumber, Customer $customer): Order
    {
        return $this->orderRepository->getByOrderNumberAndCustomer($orderNumber, $customer);
    }

    /**
     * @param array<int, int> $countsByStatusId
     * @return array<int, array{status: array{code: string, type: string, name: string}, count: int}>
     */
    protected function getOrderStatusesWithCounts(array $countsByStatusId, string $locale): array
    {
        return array_map(
            fn (OrderStatus $orderStatus): array => [
                'status' => $this->createOrderStatusData($orderStatus, $locale),
                'count' => $countsByStatusId[$orderStatus->getId()] ?? 0,
            ],
            $this->orderStatusFacade->getAll(),
        );
    }

    /**
     * @return array{code: string, type: string, name: string}
     */
    protected function createOrderStatusData(OrderStatus $orderStatus, string $locale): array
    {
        return [
            'code' => $orderStatus->getCode(),
            'type' => $orderStatus->getType(),
            'name' => $orderStatus->getName($locale),
        ];
    }
}
