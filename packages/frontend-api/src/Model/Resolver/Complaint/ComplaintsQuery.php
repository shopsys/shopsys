<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Shopsys\FrontendApiBundle\Model\Token\Exception\InvalidTokenUserMessageException;
use Symfony\Component\Security\Core\Security;

class ComplaintsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade $complaintApiFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Security $security,
    ) {
    }

    /**
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    public function complaintsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if (!$customerUser) {
            throw new InvalidTokenUserMessageException();
        }

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_ALL)) {
            return $this->getPaginatedCustomerComplaints($customerUser->getCustomer(), $argument);
        }

        return $this->getPaginatedCustomerUserComplaints($customerUser, $argument);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    protected function getPaginatedCustomerUserComplaints(
        CustomerUser $customerUser,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $search = $argument['searchInput']['search'] ?? null;

        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $search) {
            return $this->complaintApiFacade->getCustomerUserComplaintsLimitedList($customerUser, $limit, $offset, $search);
        });

        return $paginator->auto($argument, $this->complaintApiFacade->getCustomerUserComplaintsLimitedListCount($customerUser, $search));
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param \Overblog\GraphQLBundle\Definition\Argument $argument
     * @return \Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface|\GraphQL\Executor\Promise\Promise
     */
    protected function getPaginatedCustomerComplaints(
        Customer $customer,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $search = $argument['searchInput']['search'] ?? null;

        $paginator = new Paginator(function ($offset, $limit) use ($customer, $search) {
            return $this->complaintApiFacade->getCustomerComplaintsLimitedList($customer, $limit, $offset, $search);
        });

        return $paginator->auto($argument, $this->complaintApiFacade->getCustomerComplaintsLimitedListCount($customer, $search));
    }
}
