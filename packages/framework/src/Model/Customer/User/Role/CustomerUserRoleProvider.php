<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Override;
use Shopsys\FrameworkBundle\Component\Context\FrontendApiContext;
use Shopsys\FrameworkBundle\Component\Security\Role\CoreRoleProviderInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;

/**
 * Provides Frontend API specific roles
 */
class CustomerUserRoleProvider implements CoreRoleProviderInterface
{
    #[Override]
    public function getPriority(): int
    {
        return -1;
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return FrontendApiContext::class;
    }

    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        $roleAll = new Role(CustomerUserRole::ROLE_API_ALL, t('All roles'));
        $roleAll->setRoleSection(CustomerUserRoleSectionProvider::ALL);
        $roleAll->setOverwritable(false);

        $roleCollection->add($roleAll);

        foreach ($this->getCustomerUserRoles() as $role) {
            $role->setRoleSection(CustomerUserRoleSectionProvider::INDIVIDUAL);
            $role->setOverwritable(false);
            $roleCollection->add($role);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getCustomerUserRoles(): array
    {
        return [
            new Role(CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE, t('Customer self manage')),
            new Role(CustomerUserRole::ROLE_API_MANAGE_CUSTOMERS, t('Manage all customers under the user\'s company')),
            new Role(CustomerUserRole::ROLE_API_MANAGE_COMPANY_DATA, t('Manage company data (e.g., billing address)')),
            new Role(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES, t('Customer sees prices')),
            new Role(CustomerUserRole::ROLE_API_CART_AND_ORDER_CREATION, t('Cart manipulation and order creation')),
            new Role(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW, t('Access to all the orders created under the user\'s company')),
            new Role(CustomerUserRole::ROLE_API_COMPLAINT_CREATION, t('Complaint creation')),
            new Role(CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW, t('Access to all the complaints created under the user\'s company')),
        ];
    }
}
