<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Order;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Order\OrderApiFacade;
use Shopsys\FrontendApiBundle\Model\Order\OrderFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Symfony\Bundle\SecurityBundle\Security;

class OrdersQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly OrderApiFacade $orderApiFacade,
        protected readonly Security $security,
        protected readonly OrderFilterFactory $orderFilterFactory,
        protected readonly Domain $domain,
    ) {
    }

    public function ordersQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->pageSizeValidator->checkMaxPageSize($argument);

        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW)) {
            return $this->getPaginatedCustomerOrders($customerUser->getCustomer(), $argument);
        }

        return $this->getPaginatedCustomerUserOrders($customerUser, $argument);
    }

    public function orderStatusCountsQuery(Argument $argument): array
    {
        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();
        $filter = $this->orderFilterFactory->createFromArgument($argument);

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW)) {
            return $this->orderApiFacade->getCustomerOrderStatusCounts(
                $customerUser->getCustomer(),
                $filter,
                $this->domain->getLocale(),
            );
        }

        return $this->orderApiFacade->getCustomerUserOrderStatusCounts(
            $customerUser,
            $filter,
            $this->domain->getLocale(),
        );
    }

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
