<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudControllerExtension;
use Shopsys\AdministrationBundle\Controller\AbstractCrudControllerExtension;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Symfony\Component\HttpFoundation\Response;

#[CrudControllerExtension(crudController: ReviewCrudController::class)]
class ReviewCrudControllerExtension extends AbstractCrudControllerExtension
{
    #[CrudAction]
    #[CanDelete]
    public function archiveAction(int $id): Response
    {
        return new Response();
    }
}
