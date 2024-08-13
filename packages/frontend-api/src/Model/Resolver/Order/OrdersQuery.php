<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerFacade;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Component\Validation\PageSizeValidator;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Symfony\Component\Security\Core\Security;

class OrdersQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade $orderApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerFacade $customerFacade
     * @param \Symfony\Component\Security\Core\Security $security
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderFilterFactory $orderFilterFactory
     */
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly CustomerFacade $customerFacade,
        protected readonly Security $security,
        protected readonly OrderFilterFactory $orderFilterFactory,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function ordersQuery(Argument $argument): ConnectionInterface|Promise
    {
        PageSizeValidator::checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->getPaginatedCustomerOrders($customerUser->getCustomer(), $argument);
        }

        return $this->getPaginatedCustomerUserOrders($customerUser, $argument);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    protected function getPaginatedCustomerUserOrders(
        CustomerUser $customerUser,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $filter = $this->orderFilterFactory->createFromArgument($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $filter) {
            return $this->orderApiFacade->getCustomerUserOrderLimitedList($customerUser, $limit, $offset, $filter);
        });

        return $paginator->auto($argument, $this->orderApiFacade->getCustomerUserOrderCount($customerUser, $filter));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    protected function getPaginatedCustomerOrders(
        Customer $customer,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $filter = $this->orderFilterFactory->createFromArgument($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($customer, $filter) {
            return $this->orderApiFacade->getCustomerOrderLimitedList($customer, $limit, $offset, $filter);
        });

        return $paginator->auto($argument, $this->orderApiFacade->getCustomerOrderCount($customer, $filter));
    }
}
