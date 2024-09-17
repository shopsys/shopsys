<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class CustomerUserRoleProvider
{
    /**
     * @param \Symfony\Component\Security\Core\Role\RoleHierarchyInterface $roleHierarchy
     */
    public function __construct(
        protected readonly RoleHierarchyInterface $roleHierarchy,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return string[]
     */
    public function getRolesForCustomerUser(CustomerUser $customerUser): array
    {
        $roles = $this->roleHierarchy->getReachableRoleNames($customerUser->getRoles());

        return array_unique($roles);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return bool
     */
    public function canSeePrices(CustomerUser $customerUser): bool
    {
        $roles = $this->getRolesForCustomerUser($customerUser);

        return in_array(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES, $roles, true);
    }
}
