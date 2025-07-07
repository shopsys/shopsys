<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

/**
 * Interface for role registry that manages roles organized by context
 */
interface RoleRegistryInterface
{
    /**
     * Get roles for specific context
     *
     * @param class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> $context Context class name
     * @return \Shopsys\FrameworkBundle\Component\Security\Role\Role[]
     */
    public function getRoles(string $context): array;
}
