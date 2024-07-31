<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\Exception\OrderCannotBePairedException;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderApiFacade
{
    protected const int ONE_HOUR_REGISTRATION_WINDOW = 3600;

    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderRepository $orderRepository
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        protected readonly OrderFacade $orderFacade,
        protected readonly EntityManagerInterface $em,
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
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerUserOrderLimitedList(
        CustomerUser $customerUser,
        int $limit,
        int $offset,
    ): array {
        return $this->orderRepository->getCustomerUserOrderLimitedList($customerUser, $limit, $offset);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return int
     */
    public function getCustomerUserOrderCount(CustomerUser $customerUser): int
    {
        return $this->orderRepository->getCustomerUserOrderCount($customerUser);
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $orderUuid
     */
    public function pairCustomerUserWithOrderByOrderUuid(CustomerUser $customerUser, string $orderUuid): void
    {
        $order = $this->getByUuid($orderUuid);

        if ($order->getCustomerUser() !== null) {
            throw new OrderCannotBePairedException('Order is owned by another customer.');
        }

        if ($order->getEmail() !== $customerUser->getEmail()) {
            throw new OrderCannotBePairedException('Emails used in order and registration do not match.');
        }

        if ($order->getCreatedAt()->getTimestamp() < (time() - self::ONE_HOUR_REGISTRATION_WINDOW)) {
            throw new OrderCannotBePairedException('Registration for a established order is possible only within an hour of establishment of an order.');
        }

        $order->setCustomerUser($customerUser);
        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param int $limit
     * @param int $offset
     * @return \Shopsys\FrameworkBundle\Model\Order\Order[]
     */
    public function getCustomerOrderLimitedList(
        Customer $customer,
        int $limit,
        int $offset,
    ): array {
        return $this->orderRepository->getCustomerOrderLimitedList($customer, $limit, $offset);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return int
     */
    public function getCustomerOrderCount(Customer $customer): int
    {
        return $this->orderRepository->getCustomerOrderCount($customer);
    }
}
