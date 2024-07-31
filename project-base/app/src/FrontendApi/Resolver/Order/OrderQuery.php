<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderQuery as BaseOrderQuery;

/**
 * @property \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\FrontendApi\Model\Order\OrderApiFacade $orderApiFacade)
 * @method \App\Model\Order\Order getOrderForCustomerUserByUuid(\App\Model\Customer\User\CustomerUser $customerUser, string $uuid)
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \App\Model\Order\Order orderByUuidOrUrlHashQuery(string|null $uuid = null, string|null $urlHash = null, string|null $orderNumber = null)
 */
class OrderQuery extends BaseOrderQuery
{
}
