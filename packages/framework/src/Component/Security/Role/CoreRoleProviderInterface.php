<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

/**
 * Interface for core role providers that are built into the system
 * Core providers can have priority <= 0 and are not subject to priority validation
 */
interface CoreRoleProviderInterface extends RoleProviderInterface
{
}
