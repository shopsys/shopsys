<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Customer\CustomerUserRoleGroup;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class CustomerUserRoleGroupQuery extends AbstractQuery
{
    public function __construct(
        protected readonly CustomerUserRoleGroupFacade $customerUserRoleGroupFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup[]
     */
    public function customerUserRoleGroupsQuery(): array
    {
        return $this->customerUserRoleGroupFacade->getAllAssignableByCustomerUser(
            $this->currentCustomerUser->getCurrentCustomerUser(),
        );
    }
}
