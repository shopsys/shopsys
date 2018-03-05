<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class AdminLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var \Symfony\Component\Routing\Router
     */
    private $router;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade
     */
    private $administratorLoginFacade;

    /**
     * @param \Symfony\Component\Routing\Router $router
     * @param \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade
     */
    public function __construct(Router $router, AdministratorLoginFacade $administratorLoginFacade)
    {
        $this->router = $router;
        $this->administratorLoginFacade = $administratorLoginFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->administratorLoginFacade->invalidateCurrentAdministratorLoginToken();
        $url = $this->router->generate('admin_login');
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }
}
