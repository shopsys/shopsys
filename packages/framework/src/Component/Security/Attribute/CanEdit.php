<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Requires EDIT permission on the specified role
 *
 * Examples:
 * #[CanEdit('ROLE_PRODUCT')]
 * public function editAction(): Response
 *
 * With HTTP method restrictions:
 * #[CanEdit(methods: ['POST'])]
 * #[CanEdit(methods: [HttpMethod::POST])]
 * #[CanEdit(methods: ['POST', HttpMethod::PUT])] // Mixed string/enum also supported
 * public function editAction(): Response
 *
 * Or with class-level role:
 * #[ForRole('ROLE_PRODUCT')]
 * class ProductController {
 *     #[CanEdit]
 *     #[CanView(methods: ['GET'])]
 *     #[CanEdit(methods: ['POST'])]
 *     public function editAction(): Response
 * }
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CanEdit extends AbstractCanPermissionAttribute
{
    #[Override]
    public function getPermission(): Permission
    {
        return Permission::EDIT;
    }
}
