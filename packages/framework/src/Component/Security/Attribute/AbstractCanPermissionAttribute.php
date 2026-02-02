<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

abstract readonly class AbstractCanPermissionAttribute implements PermissionAttributeInterface
{
    /**
     * @param string|null $role Role name, or null to use class-level ForRole
     * @param array<string|\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod> $methods HTTP methods to restrict to (e.g., ['GET'], [HttpMethod::POST])
     */
    public function __construct(
        protected ?string $role = null,
        protected array $methods = [],
    ) {
    }

    #[Override]
    abstract public function getPermission(): Permission;

    #[Override]
    public function getRole(): ?string
    {
        return $this->role;
    }

    #[Override]
    public function getMethods(): array
    {
        return HttpMethod::validateMethods($this->methods);
    }
}
