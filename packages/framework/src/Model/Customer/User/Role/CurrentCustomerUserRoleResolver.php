<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class CurrentCustomerUserRoleResolver
{
    public function __construct(
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly CustomerUserRoleResolver $customerUserRoleResolver,
    ) {
    }

    public function canCurrentCustomerUserAssignRoleGroup(CustomerUserRoleGroup $roleGroupToAssign): bool
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return false;
        }

        return $this->customerUserRoleResolver->canCustomerUserAssignRoleGroup($currentCustomerUser, $roleGroupToAssign);
    }

    public function canCurrentCustomerUserChangeRoleGroupOfCustomerUser(CustomerUser $changedCustomerUser): bool
    {
        $currentCustomerUser = $this->currentCustomerUser->findCurrentCustomerUser();

        if ($currentCustomerUser === null) {
            return false;
        }

        return $this->customerUserRoleResolver->canCustomerUserChangeRoleGroupOfCustomerUser($currentCustomerUser, $changedCustomerUser);
    }
}
