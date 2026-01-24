<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\Attribute;

use Attribute;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;

/**
 * Requires CREATE permission on the specified role
 *
 * Examples:
 * #[CanCreate('ROLE_PRODUCT')]
 * public function newAction(): Response
 *
 * #[CanCreate('ROLE_PRODUCT', [HttpMethod::POST])]
 * public function createAction(): Response
 *
 * #[CanCreate('ROLE_PRODUCT', ['POST', 'PUT'])] // Mixed string/enum also supported
 * public function saveAction(): Response
 *
 * Or with class-level role:
 * #[ForRole('ROLE_PRODUCT')]
 * class ProductController {
 *     #[CanCreate]
 *     public function newAction(): Response
 * }
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class CanCreate extends AbstractCanPermissionAttribute
{
    #[Override]
    public function getPermission(): Permission
    {
        return Permission::CREATE;
    }
}
