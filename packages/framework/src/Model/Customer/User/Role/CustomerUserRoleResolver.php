<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class CustomerUserRoleResolver
{
    public function __construct(
        protected readonly CustomerUserRoleHierarchyProvider $customerUserRoleHierarchyProvider,
        protected readonly Security $security,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    /**
     * @return string[]
     */
    public function getRolesForCustomerUser(CustomerUser $customerUser): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            'customerUserRoles',
            function () use ($customerUser) {
                $hierarchy = $this->customerUserRoleHierarchyProvider->generateRoleHierarchy();
                $roleHierarchy = new RoleHierarchy($hierarchy);

                $roles = $roleHierarchy->getReachableRoleNames($customerUser->getRoles());

                return array_unique($roles);
            },
            $customerUser->getId(),
        );
    }

    public function canCustomerUserSeePrices(?CustomerUser $customerUser): bool
    {
        if ($customerUser === null) {
            return true;
        }

        $roles = $this->getRolesForCustomerUser($customerUser);

        return in_array(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES, $roles, true);
    }

    public function canCurrentCustomerUserSeePrices(): bool
    {
        if ($this->security->getUser() === null) {
            return true;
        }

        return $this->security->isGranted(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES);
    }
}
