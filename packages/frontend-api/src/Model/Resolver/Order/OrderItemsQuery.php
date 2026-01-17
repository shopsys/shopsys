<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilter;
use Shopsys\FrontendApiBundle\Model\Order\OrderItemsFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Symfony\Bundle\SecurityBundle\Security;

class OrderItemsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly OrderItemApiFacade $orderItemApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderItemsFilterFactory $orderItemsFilterFactory,
        protected readonly Security $security,
    ) {
    }

    public function orderItemsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();

        $filter = $this->orderItemsFilterFactory->createFromArgument($argument);

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW)) {
            return $this->getPaginatedCustomerOrderItems($customerUser->getCustomer(), $filter, $argument);
        }

        return $this->getPaginatedCustomerUserOrderItems($customerUser, $filter, $argument);
    }

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
