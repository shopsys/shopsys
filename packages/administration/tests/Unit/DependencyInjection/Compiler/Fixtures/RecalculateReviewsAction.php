<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\FrameworkBundle\Component\Security\Attribute\SuperAdminOnly;
use Symfony\Component\HttpFoundation\Response;

#[CrudAction(crudController: ReviewCrudController::class, name: 'recalculate')]
#[SuperAdminOnly]
class RecalculateReviewsAction
{
    public function __invoke(): Response
    {
        return new Response();
    }
}
