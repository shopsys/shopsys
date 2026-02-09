<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

/**
 * Requires one or more specific roles to access the controller action
 *
 * Example:
 * #[RequireRole(SystemRole::ADMIN)]
 * #[RequireRole(['ROLE_MANAGER', 'ROLE_SUPERVISOR'])]
 * #[RequireRole('ROLE_ADMIN', [HttpMethod::POST])]
 * #[RequireRole(['ROLE_MANAGER'], ['GET', HttpMethod::HEAD])]
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class RequireRole
{
    /**
     * @var string[]
     */
    public array $roles;

    /**
     * @param array<string|\Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod> $methods
     */
    public function __construct(
        string|array $roles,
        protected array $methods = [],
    ) {
        $this->roles = is_string($roles) ? [$roles] : $roles;
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
