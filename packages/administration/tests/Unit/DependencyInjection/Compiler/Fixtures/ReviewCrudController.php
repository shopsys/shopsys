<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\DependencyInjection\Compiler\Fixtures;

use Shopsys\AdministrationBundle\Component\Attributes\CrudAction;
use Shopsys\AdministrationBundle\Component\Attributes\CrudController;
use Shopsys\AdministrationBundle\Controller\AbstractCrudController;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CrudController(stdClass::class)]
class ReviewCrudController extends AbstractCrudController
{
    #[CrudAction]
    #[CanEdit]
    #[CsrfProtection]
    public function approveAction(int $id): Response
    {
        return new Response();
    }

    #[CrudAction]
    #[CanEdit]
    public function moveAction(Request $request, int $id, int $position, string $direction = 'down'): Response
    {
        return new Response();
    }

    #[CrudAction(name: 'export_all', path: '/export.csv', methods: ['GET'], requirements: ['_format' => 'csv'], defaults: ['_format' => 'csv'], condition: 'request.isXmlHttpRequest()')]
    #[CanView]
    public function exportAction(): Response
    {
        return new Response();
    }
}
