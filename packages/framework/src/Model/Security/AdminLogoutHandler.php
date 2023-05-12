<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class AdminLogoutHandler
{
    /**
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade $administratorLoginFacade
     */
    public function __construct(protected readonly RouterInterface $router, protected readonly AdministratorLoginFacade $administratorLoginFacade)
    {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function onLogoutSuccess(Request $request)
    {
        $this->administratorLoginFacade->invalidateCurrentAdministratorLoginToken();
        $url = $this->router->generate('admin_login');
        $request->getSession()->remove(LoginAsUserFacade::SESSION_LOGIN_AS);
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }
}
