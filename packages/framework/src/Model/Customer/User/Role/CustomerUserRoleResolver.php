<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class CustomerUserRoleResolver
{
    protected const string CUSTOMER_USER_ROLES_CACHE_NAMESPACE = 'customerUserRoles';
    protected const string CUSTOMER_USER_ROLE_GROUP_ROLES_CACHE_NAMESPACE = 'customerUserRoleGroupRoles';

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
            static::CUSTOMER_USER_ROLES_CACHE_NAMESPACE,
            fn (): array => $this->getReachableRoleNames($customerUser->getRoles()),
            $customerUser->getId(),
        );
    }

    public function resetRolesCacheForCustomerUser(CustomerUser $customerUser): void
    {
        $this->inMemoryCache->deleteItem(static::CUSTOMER_USER_ROLES_CACHE_NAMESPACE, $customerUser->getId());
    }

    /**
     * @return string[]
     */
    protected function getRolesForRoleGroup(CustomerUserRoleGroup $customerUserRoleGroup): array
    {
        return $this->inMemoryCache->getOrSaveValue(
            static::CUSTOMER_USER_ROLE_GROUP_ROLES_CACHE_NAMESPACE,
            fn (): array => $this->getReachableRoleNames($customerUserRoleGroup->getRoles()),
            $customerUserRoleGroup->getId(),
        );
    }

    public function canCustomerUserAssignRoleGroup(
        CustomerUser $customerUser,
        CustomerUserRoleGroup $roleGroupToAssign,
    ): bool {
        $rolesMissingFromCustomerUser = array_diff(
            $this->getRolesForRoleGroup($roleGroupToAssign),
            $this->getRolesForCustomerUser($customerUser),
        );

        return count($rolesMissingFromCustomerUser) === 0;
    }

    public function canCustomerUserChangeRoleGroupOfCustomerUser(
        CustomerUser $changingCustomerUser,
        CustomerUser $changedCustomerUser,
    ): bool {
        if ($changingCustomerUser->getCustomer()->getId() !== $changedCustomerUser->getCustomer()->getId()) {
            return false;
        }

        if (!in_array(CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS, $this->getRolesForCustomerUser($changingCustomerUser), true)) {
            return false;
        }

        return $this->canCustomerUserAssignRoleGroup($changingCustomerUser, $changedCustomerUser->getRoleGroup());
    }

    /**
     * @param string[] $roles
     * @return string[]
     */
    protected function getReachableRoleNames(array $roles): array
    {
        $roleHierarchy = new RoleHierarchy($this->customerUserRoleHierarchyProvider->generateRoleHierarchy());

        return array_values(array_unique($roleHierarchy->getReachableRoleNames($roles)));
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
