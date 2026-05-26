<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Requires a specific permission on a role to access the controller action
 *
 * Example:
 * #[RequirePermission('ROLE_PRODUCT', Permission::VIEW)]
 * #[RequirePermission('ROLE_ORDER', Permission::EDIT)]
 * #[RequirePermission('ROLE_PRODUCT', Permission::CREATE, [HttpMethod::POST])]
 * #[RequirePermission('ROLE_ORDER', Permission::DELETE, ['DELETE', HttpMethod::POST])]
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final readonly class RequirePermission
{
    /**
     * @param array<string|\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod> $methods
     */
    public function __construct(
        public string $role,
        public Permission $permission,
        private array $methods = [],
    ) {
    }

    /**
     * Get methods as normalized HttpMethod enum array
     *
     * @return \Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod[]
     */
    public function getMethods(): array
    {
        return HttpMethod::validateMethods($this->methods);
    }
}
