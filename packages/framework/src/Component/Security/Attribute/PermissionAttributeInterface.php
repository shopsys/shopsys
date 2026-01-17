<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Interface for security attributes that define permission requirements
 */
interface PermissionAttributeInterface
{
    /**
     * Get the permission required by this attribute
     */
    public function getPermission(): Permission;

    /**
     * Get the role name (if specified directly on attribute)
     */
    public function getRole(): ?string;

    /**
     * Get the HTTP methods this attribute applies to
     *
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod[]
     */
    public function getMethods(): array;
}
