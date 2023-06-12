<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Order;

use GraphQL\Server\RequestError;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\OrderQuery as BaseOrderQuery;

/**
 * @property \App\FrontendApi\Model\Order\OrderFacade $frontendApiOrderFacade
 * @property \App\Model\Order\OrderFacade $orderFacade
 * @method __construct(\App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Order\OrderFacade $orderFacade, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\FrontendApi\Model\Order\OrderFacade $frontendApiOrderFacade)
 * @method \App\Model\Order\Order getOrderForCustomerUserByUuid(\App\Model\Customer\User\CustomerUser $customerUser, string $uuid)
 */
class OrderQuery extends BaseOrderQuery
{
    /**
     * @param string|null $uuid
     * @param string|null $urlHash
     * @param string|null $orderNumber
     * @return \App\Model\Order\Order
     */
    public function orderByUuidOrUrlHashQuery(
        ?string $uuid = null,
        ?string $urlHash = null,
        ?string $orderNumber = null,
    ): Order {
        /** @var \App\Model\Customer\User\CustomerUser|null $customerUser */
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        try {
            if ($orderNumber !== null && $customerUser !== null) {
                return $this->frontendApiOrderFacade->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
            }
            if ($uuid !== null && $customerUser !== null) {
                return $this->getOrderForCustomerUserByUuid($customerUser, $uuid);
            }
            if ($urlHash !== null) {
                return $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
            }
        } catch (OrderNotFoundException $orderNotFoundException) {
            throw new OrderNotFoundUserError('Order not found');
        }

        throw new RequestError('You need to be logged in or provide argument \'urlHash\'.');
    }
}
