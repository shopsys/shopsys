<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\RoleGroup;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Shopsys\FrameworkBundle\Model\Administrator\RoleGroup\Exception\SystemManagedRoleGroupException;

/**
 * Doctrine event listener that prevents modification of system-managed AdministratorRoleGroup entities
 *
 * This listener acts as a security fallback to prevent any direct entity manager operations
 * that might bypass controller/facade validation layers. It specifically protects role groups
 * that are managed by the system (systemManaged = true) from being updated or deleted.
 */
#[AsEntityListener(event: Events::preUpdate, entity: AdministratorRoleGroup::class)]
#[AsEntityListener(event: Events::preRemove, entity: AdministratorRoleGroup::class)]
class SystemManagedRoleGroupProtectionListener
{
    public function __invoke(AdministratorRoleGroup $administratorRoleGroup): void
    {
        if ($administratorRoleGroup->isSystemManaged()) {
            throw new SystemManagedRoleGroupException($administratorRoleGroup->getName());
        }
    }
}
