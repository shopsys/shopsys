<?php

namespace Shopsys\FrameworkBundle\Model\Security;

use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class FrontLogoutHandler implements LogoutSuccessHandlerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade
     */
    private $orderFlowFacade;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router, OrderFlowFacade $orderFlowFacade)
    {
        $this->router = $router;
        $this->orderFlowFacade = $orderFlowFacade;
    }

    public function onLogoutSuccess(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        $this->orderFlowFacade->resetOrderForm();
        $url = $this->router->generate('front_homepage');
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }
}
