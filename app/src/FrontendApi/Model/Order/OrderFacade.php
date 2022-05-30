<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use App\Model\Customer\User\CustomerUser;
use App\Model\Order\Order;
use App\Model\Order\OrderFacade as AppOrderFacade;
use DateTime;
use Overblog\GraphQLBundle\Error\UserError;
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
    /**
     * @var \App\Model\Order\OrderFacade
     */
    private AppOrderFacade $appOrderFacade;

    /**
     * @param \App\FrontendApi\Model\Order\OrderRepository $orderRepository
     * @param \App\Model\Order\OrderFacade $appOrderFacade
     */
    public function __construct(
        OrderRepository $orderRepository,
        AppOrderFacade $appOrderFacade
    ) {
        parent::__construct($orderRepository);

        $this->appOrderFacade = $appOrderFacade;
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
            throw new UserError('You cannot request page content for order older than 5 minutes.');
        }

        return $this->appOrderFacade->getOrderSentPageContent($order->getId());
    }
}
