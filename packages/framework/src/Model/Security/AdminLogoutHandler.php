<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class AdminLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade
     */
    private $administratorLoginFacade;

    public function __construct(RouterInterface $router, AdministratorLoginFacade $administratorLoginFacade)
    {
        $this->router = $router;
        $this->administratorLoginFacade = $administratorLoginFacade;
    }

    public function onLogoutSuccess(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->administratorLoginFacade->invalidateCurrentAdministratorLoginToken();
        $url = $this->router->generate('admin_login');
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }
}
