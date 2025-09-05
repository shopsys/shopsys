<?php

declare(strict_types=1);

namespace App\Model\Security;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleProviderInterface;

/**
 * Custom role provider for project-specific roles and role modifications
 */
class AdminRoleProvider implements RoleProviderInterface
{
    /**
     * @return int
     */
    #[Override]
    public function getPriority(): int
    {
        return 100; // High priority - can modify core roles
    }

    /**
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext>
     */
    #[Override]
    public function getTargetContext(): string
    {
        return AdminContext::class;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\Role\RoleCollection $roleCollection
     */
    #[Override]
    public function configureRoles(RoleCollection $roleCollection): void
    {
        // Example 1: Add custom roles to custom sections
        // $roleCollection->add(new Role('ROLE_ANALYTICS', t('Analytics'), [Permission::FULL]));
        // $roleCollection->add(new Role('ROLE_MAILCHIMP', t('MailChimp Integration'), [Permission::VIEW]));

        // Example 2: Modify existing core roles
        // if ($roleCollection->has('ROLE_ORDER')) {
        //     $roleCollection->edit('ROLE_ORDER', function (Role $role) {
        //         $role->setName(t('Enhanced Order Management'));
        //         $role->setAvailablePermissions([Permission::VIEW, Permission::EDIT]);
        //     });
        // }
    }
}
