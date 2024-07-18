<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade as BaseOrderApiFacade;

/**
 * @property \App\FrontendApi\Model\Order\OrderRepository $orderRepository
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuid(string $orderUuid)
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderRepository $orderRepository, \App\Model\Order\OrderFacade $orderFacade, \Doctrine\ORM\EntityManagerInterface $em)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findLastOrderByCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method pairCustomerUserWithOrderByOrderUuid(\App\Model\Customer\User\CustomerUser $customerUser, string $orderUuid)
 */
class OrderApiFacade extends BaseOrderApiFacade
{
}
