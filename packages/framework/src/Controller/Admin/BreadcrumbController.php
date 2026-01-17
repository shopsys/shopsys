<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\Response;

class BreadcrumbController extends AdminBaseController
{
    public function __construct(
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    #[RequireRole(SystemRole::ADMIN)]
    public function indexAction(): Response
    {
        return $this->render('@ShopsysAdministration/partial/breadcrumb.html.twig', [
            'breadcrumbOverrider' => $this->breadcrumbOverrider,
        ]);
    }
}
