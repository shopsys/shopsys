<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\Security;

class CustomerUserRoleResolver
{
    /**
     * @param \Symfony\Component\Security\Core\Role\RoleHierarchyInterface $roleHierarchy
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(
        protected readonly RoleHierarchyInterface $roleHierarchy,
        protected readonly Security $security,
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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser|null $customerUser
     * @return bool
     */
    public function canCustomerUserSeePrices(?CustomerUser $customerUser): bool
    {
        if ($customerUser === null) {
            return true;
        }

        $roles = $this->getRolesForCustomerUser($customerUser);

        return in_array(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES, $roles, true);
    }

    /**
     * @return bool
     */
    public function canCurrentCustomerUserSeePrices(): bool
    {
        if ($this->security->getUser() === null) {
            return true;
        }

        return $this->security->isGranted(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES);
    }
}
