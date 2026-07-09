<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Order;

use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade as BaseOrderApiFacade;

/**
 * @property \App\FrontendApi\Model\Order\OrderRepository $orderRepository
 * @method \App\Model\Order\Order[] getCustomerUserOrderLimitedList(\App\Model\Customer\User\CustomerUser $customerUser, int $limit, int $offset, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter)
 * @method int getCustomerUserOrderCount(\App\Model\Customer\User\CustomerUser $customerUser, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter)
 * @method \App\Model\Order\Order getByUuidAndCustomerUser(string $uuid, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order getByUuid(string $orderUuid)
 * @method \App\Model\Order\Order getAuthorizedOrder(string $orderUuid, string|null $orderUrlHash)
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\FrontendApi\Model\Order\OrderRepository $orderRepository, \App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusFacade $orderStatusFacade, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Symfony\Bundle\SecurityBundle\Security $security)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomerUser(string $orderNumber, \App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order|null findLastOrderByCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method \App\Model\Order\Order[] getCustomerOrderLimitedList(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer, int $limit, int $offset, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $orderFilter)
 * @method \App\Model\Order\Order getByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Order\Order getByOrderNumberAndCustomer(string $orderNumber, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method array<int, array{status: array{code: string, type: string, name: string}, count: int}> getCustomerUserOrderStatusCounts(\App\Model\Customer\User\CustomerUser $customerUser, \Shopsys\FrontendApiBundle\Model\Order\OrderFilter $filter, string $locale)
 * @method array{code: string, type: string, name: string} createOrderStatusData(\App\Model\Order\Status\OrderStatus $orderStatus, string $locale)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \App\Model\Order\Order getByUrlHashWithMatchingUuid(string $orderUuid, string $orderUrlHash)
 * @method \App\Model\Order\Order getAuthorizedOrderForCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser, string $orderUuid)
 */
class OrderApiFacade extends BaseOrderApiFacade
{
}
