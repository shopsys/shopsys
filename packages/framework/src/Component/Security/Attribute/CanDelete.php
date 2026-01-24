<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Requires DELETE permission on the specified role
 *
 * Examples:
 * #[CanDelete('ROLE_PRODUCT')]
 * public function deleteAction(): Response
 *
 * #[CanDelete('ROLE_PRODUCT', [HttpMethod::DELETE])]
 * public function removeAction(): Response
 *
 * #[CanDelete('ROLE_PRODUCT', ['DELETE', HttpMethod::POST])] // Mixed string/enum also supported
 * public function destroyAction(): Response
 *
 * Or with class-level role:
 * #[ForRole('ROLE_PRODUCT')]
 * class ProductController {
 *     #[CanDelete]
 *     public function deleteAction(): Response
 * }
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CanDelete extends AbstractCanPermissionAttribute
{
    #[Override]
    public function getPermission(): Permission
    {
        return Permission::DELETE;
    }
}
