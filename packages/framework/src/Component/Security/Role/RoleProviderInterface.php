<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Role;

/**
 * Interface for classes that provide role definitions
 */
interface RoleProviderInterface
{
    /**
     * Get provider priority. Lower priority means the provider is processed earlier.
     * Providers with lower priority are processed first, so higher priority providers can override roles
     *
     * Core roles are processed by Shopsys\AdministrationBundle\Component\Security\Role\CoreAdminRoleProvider and are always processed first.
     */
    public function getPriority(): int;

    /**
     * Get the context for which this provider provides roles
     *
     * @return class-string<\Shopsys\FrameworkBundle\Component\Context\AbstractContext> FQCN of the context class
     */
    public function getTargetContext(): string;

    /**
     * Configure roles in the collection.
     * This method is called in priority order (lower priority first).
     */
    public function configureRoles(RoleCollection $roleCollection): void;
}
