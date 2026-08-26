<?php

declare(strict_types=1);

namespace App\Model\Order;

use Shopsys\FrameworkBundle\Model\Order\OrderRepository as BaseOrderRepository;

/**
 * @method \App\Model\Order\Order|null findById(int $id)
 * @method \App\Model\Order\Order getById(int $id)
 * @method \App\Model\Order\Order[] getCustomerUserOrderList(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getOrderListForEmailByDomainId(string $email, int $domainId)
 * @method \App\Model\Order\Order getByUrlHashAndDomain(string $urlHash, int $domainId)
 * @method \App\Model\Order\Order|null findByUrlHashIncludingDeletedOrders(string $urlHash)
 * @method bool isOrderStatusUsed(\App\Model\Order\Status\OrderStatus $orderStatus)
 * @method \App\Model\Order\Order getByUuid(string $uuid)
 * @method \App\Model\Order\Order[] getAllWithoutTrackingNumberByTransportType(string $transportType)
 * @method \App\Model\Order\Order[] getLastCustomerOrdersByLimit(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, int $limit, string $locale)
 * @method array<int, \App\Model\Order\Order> findByIds(int[] $ids)
 */
class OrderRepository extends BaseOrderRepository
{
}
