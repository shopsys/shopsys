<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\FrontendApi\Model\Order\Exception\OrderCannotBePairedException;
use App\FrontendApi\Model\Order\Exception\OrderSentPageNotAvailableUserError;
use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade as AppOrderFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade as BaseOrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderRepository;

/**
 * @property \App\FrontendApi\Model\Order\OrderRepository $orderRepository
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 */
class OrderFacade extends BaseOrderFacade
{
    private const ONE_HOUR_REGISTRATION_WINDOW = 3600;

    /**
     * @param \App\FrontendApi\Model\Order\OrderRepository $orderRepository
     * @param \App\Model\Order\OrderFacade $appOrderFacade
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        OrderRepository $orderRepository,
        private readonly AppOrderFacade $appOrderFacade,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct($orderRepository);
    }

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

    /**
     * @param string $orderUuid
     * @return string
     */
    public function getOrderSentPageContent(string $orderUuid): string
    {
        $order = $this->getByUuid($orderUuid);
        $fiveMinutesAgo = new DateTime('-5 minutes');

        if ($order->getCreatedAt() < $fiveMinutesAgo) {
            throw new OrderSentPageNotAvailableUserError('You cannot request page content for order older than 5 minutes.');
        }

        return $this->appOrderFacade->getOrderSentPageContent($order->getId());
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param string $orderUuid
     */
    public function pairCustomerUserWithOrderByOrderUuid(CustomerUser $customerUser, string $orderUuid): void
    {
        $order = $this->orderRepository->getByUuid($orderUuid);
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
}
