<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Output\Connection;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Symfony\Component\Security\Core\Security;

class OrderItemsSearchQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory $orderItemsFilterFactory
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderItemsFilterFactory $orderItemsFilterFactory,
        protected readonly Security $security,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function orderItemsSearchQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $searchInput = $argument['searchInput']['search'];

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $search = (string)$searchInput;
        $filter = $this->orderItemsFilterFactory->createFromArgument($argument);

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->getPaginatedCustomerOrderItemsSearchList($customerUser->getCustomer(), $search, $filter, $argument);
        }

        return $this->getPaginatedCustomerUserOrderItemsSearchList($customerUser, $search, $filter, $argument);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param string $search
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\Output\Connection
     */
    protected function getPaginatedCustomerUserOrderItemsSearchList(
        CustomerUser $customerUser,
        string $search,
        OrderItemsFilter $filter,
        Argument $argument,
    ): Promise|Connection {
        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $search, $filter) {
            return $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedSearchList($search, $customerUser, $limit, $offset, $filter);
        });

        return $paginator->auto($argument, $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedSearchListCount($search, $customerUser, $filter));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param string $search
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    protected function getPaginatedCustomerOrderItemsSearchList(
        Customer $customer,
        string $search,
        OrderItemsFilter $filter,
        Argument $argument,
    ): Promise|ConnectionInterface {
        $paginator = new Paginator(function ($offset, $limit) use ($customer, $search, $filter) {
            return $this->orderItemApiFacade->getCustomerOrderItemsLimitedSearchList($search, $customer, $limit, $offset, $filter);
        });

        return $paginator->auto($argument, $this->orderItemApiFacade->getCustomerOrderItemsLimitedSearchListCount($search, $customer, $filter));
    }
}
