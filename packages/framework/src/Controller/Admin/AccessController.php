<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccessController extends AdminBaseController
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/access-denied/')]
    #[RequireRole(SystemRole::ADMIN)]
    public function deniedAction(Request $request): Response
    {
        $this->addErrorFlash(
            t('You are not allowed to access the requested page. Please ask your administrator to grant you access to the requested page.'),
        );

        $response = $this->forward(DefaultController::class . '::dashboardAction');

        $response->setStatusCode(Response::HTTP_FORBIDDEN);

        return $response;
    }
}
