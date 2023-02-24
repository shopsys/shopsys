<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderFacade as FrontendApiOrderFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\InvalidAccessUserError;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;

class OrderQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFacade $frontendApiOrderFacade
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
        protected readonly FrontendApiOrderFacade $frontendApiOrderFacade
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlHash
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function orderByUuidOrUrlHashQuery(?string $uuid = null, ?string $urlHash = null): Order
    {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        try {
            if ($uuid !== null && $customerUser !== null) {
                return $this->getOrderForCustomerUserByUuid($customerUser, $uuid);
            }

            if ($urlHash !== null) {
                return $this->orderFacade->getByUrlHashAndDomain($urlHash, $this->domain->getId());
            }
        } catch (OrderNotFoundException $orderNotFoundException) {
            throw new OrderNotFoundUserError($orderNotFoundException->getMessage());
        }

        throw new InvalidAccessUserError('You need to be logged in or provide argument \'urlHash\'.');
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    protected function getOrderForCustomerUserByUuid(
        CustomerUser $customerUser,
        string $uuid
    ): Order {
        return $this->frontendApiOrderFacade->getByUuidAndCustomerUser($uuid, $customerUser);
    }
}
