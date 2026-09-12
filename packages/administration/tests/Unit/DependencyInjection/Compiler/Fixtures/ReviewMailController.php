<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequirePermission;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Symfony\Component\HttpFoundation\Response;

/**
 * A plain controller declaring an action of a CRUD controller it does not belong to
 */
class ReviewMailController
{
    #[CrudAction(crudController: ReviewCrudController::class)]
    #[RequirePermission(role: 'ROLE_MAILING', permission: Permission::EDIT)]
    public function sendEmailAction(int $id): Response
    {
        return new Response();
    }
}
