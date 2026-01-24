<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Requires VIEW permission on the specified role
 *
 * Examples:
 * #[CanView('ROLE_PRODUCT')]
 * public function listAction(): Response
 *
 * #[CanView('ROLE_PRODUCT', [HttpMethod::GET])]
 * public function detailAction(): Response
 *
 * #[CanView('ROLE_PRODUCT', ['GET', HttpMethod::HEAD])] // Mixed string/enum also supported
 * public function showAction(): Response
 *
 * Or with class-level role:
 * #[ForRole('ROLE_PRODUCT')]
 * class ProductController {
 *     #[CanView]
 *     public function listAction(): Response
 * }
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CanView extends AbstractCanPermissionAttribute
{
    #[Override]
    public function getPermission(): Permission
    {
        return Permission::VIEW;
    }
}
