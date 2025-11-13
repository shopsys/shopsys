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
    /**
     * @return int
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection $roleCollection
     */
    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        $roleCollection->add(new Role(CustomerUserRole::ROLE_API_ALL, t('B2B data and user management'), allowOverwrite: false));

        foreach ($this->getCustomerUserRoles() as $role) {
            $roleCollection->add($role);
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    protected function getCustomerUserRoles(): array
    {
        return [
            new Role(CustomerUserRole::ROLE_API_CUSTOMER_SELF_MANAGE, t('Customer self manage'), allowOverwrite: false),
            new Role(CustomerUserRole::ROLE_API_CUSTOMER_SEES_PRICES, t('Customer sees prices'), allowOverwrite: false),
            new Role(CustomerUserRole::ROLE_API_CART_AND_ORDER_CREATION, t('Cart manipulation and order creation'), allowOverwrite: false),
            new Role(CustomerUserRole::ROLE_API_COMPANY_ORDERS_VIEW, t('Access to all the orders created under the user\'s company'), allowOverwrite: false),
            new Role(CustomerUserRole::ROLE_API_COMPLAINT_CREATION, t('Complaint creation'), allowOverwrite: false),
            new Role(CustomerUserRole::ROLE_API_COMPANY_COMPLAINTS_VIEW, t('Access to all the complaints created under the user\'s company'), allowOverwrite: false),
        ];
    }
}
