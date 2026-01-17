<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade as BaseOrderApiFacade;

/**
 * @property \App\FrontendApi\Model\Order\OrderRepository $orderRepository
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList( $customerUser,  $limit,  $offset,  $filter)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuid(string $orderUuid)
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderRepository $orderRepository, \App\Model\Order\OrderFacade $orderFacade)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findLastOrderByCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerOrderLimitedList( $customer,  $limit,  $offset,  $orderFilter)
 * @method \App\Model\Order\Order getByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomer(string $orderNumber, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 */
class OrderApiFacade extends BaseOrderApiFacade
{
}
