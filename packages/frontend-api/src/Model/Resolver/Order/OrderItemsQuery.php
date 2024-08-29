<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use App\Model\Customer\User\CurrentCustomerUser;
use App\Model\Customer\User\CustomerUser;
use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Symfony\Component\Security\Core\Security;

class OrderItemsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade $orderItemApiFacade
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
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
    public function orderItemsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        $filter = $this->orderItemsFilterFactory->createFromArgument($argument);

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->getPaginatedCustomerOrderItems($customerUser->getCustomer(), $filter, $argument);
        }

        return $this->getPaginatedCustomerUserOrderItems($customerUser, $filter, $argument);
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    protected function getPaginatedCustomerUserOrderItems(
        CustomerUser $customerUser,
        OrderItemsFilter $filter,
        Argument $argument,
    ): Promise|ConnectionInterface {
        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $filter) {
            return $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedList(
                $customerUser,
                $limit,
                $offset,
                $filter,
            );
        });

        return $paginator->auto(
            $argument,
            $this->orderItemApiFacade->getCustomerUserOrderItemsLimitedListCount($customerUser, $filter),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter $filter
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \GraphQL\Executor\Promise\Promise|\Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface
     */
    protected function getPaginatedCustomerOrderItems(
        Customer $customer,
        OrderItemsFilter $filter,
        Argument $argument,
    ): Promise|ConnectionInterface {
        $paginator = new Paginator(function ($offset, $limit) use ($customer, $filter) {
            return $this->orderItemApiFacade->getCustomerOrderItemsLimitedList(
                $customer,
                $limit,
                $offset,
                $filter,
            );
        });

        return $paginator->auto(
            $argument,
            $this->orderItemApiFacade->getCustomerOrderItemsLimitedListCount($customer, $filter),
        );
    }
}
