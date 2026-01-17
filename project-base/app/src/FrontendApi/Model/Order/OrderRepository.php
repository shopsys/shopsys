<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderRepository as BaseOrderRepository;

/**
 * @method \App\Model\Order\Order|null findByUuidAndCustomerUser( $uuid,  $customerUser)
 * @method \App\Model\Order\Order|null findByUuidAndUrlHash( $uuid,  $urlHash)
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList( $customerUser,  $limit,  $offset,  $filter = null)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerOrderLimitedList( $customer,  $limit,  $offset,  $orderFilter)
 * @method \App\Model\Order\Order|null findByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Order\Order getByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Order\Order|null findByOrderNumberAndCustomer(string $orderNumber, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomer(string $orderNumber, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \Doctrine\ORM\QueryBuilder createCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser)
 */
class OrderRepository extends BaseOrderRepository
{
}
