<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Server\RequestError;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrameworkBundle\Model\Order\Exception\OrderNotFoundException;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Resolver\Order\Exception\OrderNotFoundUserError;
use Symfony\Component\Security\Core\Security;

class OrderQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFacade $orderFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderFacade $orderFacade,
        protected readonly Domain $domain,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly Security $security,
    ) {
    }

    /**
     * @param string|null $uuid
     * @param string|null $urlHash
     * @param string|null $orderNumber
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    public function orderByUuidOrUrlHashQuery(
        ?string $uuid = null,
        ?string $urlHash = null,
        ?string $orderNumber = null,
    ): Order {
        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        try {
            if ($orderNumber !== null && $customerUser !== null) {
                return $this->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $uuid
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    protected function getOrderForCustomerUserByUuid(CustomerUser $customerUser, string $uuid): Order
    {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->orderApiFacade->getByUuidAndCustomer($uuid, $customerUser->getCustomer());
        }

        return $this->orderApiFacade->getByUuidAndCustomerUser($uuid, $customerUser);
    }

    /**
     * @param string $orderNumber
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Order
     */
    protected function getByOrderNumberAndCustomerUser(string $orderNumber, CustomerUser $customerUser): Order
    {
        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->orderApiFacade->getByOrderNumberAndCustomer($orderNumber, $customerUser->getCustomer());
        }

        return $this->orderApiFacade->getByOrderNumberAndCustomerUser($orderNumber, $customerUser);
    }
}
