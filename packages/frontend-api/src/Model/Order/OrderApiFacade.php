<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Order;

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
}
