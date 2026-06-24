<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Complaint;

use GraphQL\Executor\Promise\Promise;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionInterface;
use Overblog\GraphQLBundle\Relay\Connection\Paginator;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Complaint\ComplaintResolutionEnum;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRole;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintApiFacade;
use Shopsys\FrontendApiBundle\Model\Complaint\ComplaintFilterFactory;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;
use Symfony\Bundle\SecurityBundle\Security;

class ComplaintsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly ComplaintApiFacade $complaintApiFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Security $security,
        protected readonly ComplaintResolutionEnum $complaintResolutionEnum,
        protected readonly ComplaintFilterFactory $complaintFilterFactory,
        protected readonly Domain $domain,
    ) {
    }

    public function complaintsQuery(Argument $argument): ConnectionInterface|Promise
    {
        $this->setDefaultFirstOffsetIfNecessary($argument);

        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW)) {
            return $this->getPaginatedCustomerComplaints($customerUser->getCustomer(), $argument);
        }

        return $this->getPaginatedCustomerUserComplaints($customerUser, $argument);
    }

    public function complaintResolutionQuery(): array
    {
        return $this->complaintResolutionEnum->serialize();
    }

    public function complaintStatusCountsQuery(Argument $argument): array
    {
        $customerUser = $this->currentCustomerUser->getCurrentCustomerUser();
        $filter = $this->complaintFilterFactory->createFromArgument($argument);

        if ($this->security->isGranted(CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW)) {
            return $this->complaintApiFacade->getCustomerComplaintStatusCounts(
                $customerUser->getCustomer(),
                $filter,
                $this->domain->getLocale(),
            );
        }

        return $this->complaintApiFacade->getCustomerUserComplaintStatusCounts(
            $customerUser,
            $filter,
            $this->domain->getLocale(),
        );
    }

    protected function getPaginatedCustomerUserComplaints(
        CustomerUser $customerUser,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $filter = $this->complaintFilterFactory->createFromArgument($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($customerUser, $filter) {
            return $this->complaintApiFacade->getCustomerUserComplaintsLimitedList(
                $customerUser,
                $limit,
                $offset,
                $filter,
            );
        });

        return $paginator->auto(
            $argument,
            $this->complaintApiFacade->getCustomerUserComplaintsLimitedListCount($customerUser, $filter),
        );
    }

    protected function getPaginatedCustomerComplaints(
        Customer $customer,
        Argument $argument,
    ): ConnectionInterface|Promise {
        $filter = $this->complaintFilterFactory->createFromArgument($argument);

        $paginator = new Paginator(function ($offset, $limit) use ($customer, $filter) {
            return $this->complaintApiFacade->getCustomerComplaintsLimitedList(
                $customer,
                $limit,
                $offset,
                $filter,
            );
        });

        return $paginator->auto(
            $argument,
            $this->complaintApiFacade->getCustomerComplaintsLimitedListCount($customer, $filter),
        );
    }
}
