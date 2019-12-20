<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Security;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class FrontLogoutHandler implements LogoutSuccessHandlerInterface, AuthenticationFailureHandlerInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender
     */
    private $flashMessageSender;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade
     */
    protected $orderFlowFacade;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    protected $router;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\FlashMessage\FlashMessageSender $flashMessageSender
     * @param \Symfony\Component\Routing\RouterInterface $router
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderFlowFacade $orderFlowFacade
     * @param \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer $currentCustomer
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(FlashMessageSender $flashMessageSender, RouterInterface $router, OrderFlowFacade $orderFlowFacade, CurrentCustomer $currentCustomer, Domain $domain)
    {
        $this->router = $router;
        $this->orderFlowFacade = $orderFlowFacade;
        $this->currentCustomer = $currentCustomer;
        $this->domain = $domain;
        $this->flashMessageSender = $flashMessageSender;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onLogoutSuccess(Request $request): Response
    {
        $this->orderFlowFacade->resetOrderForm();
        $url = $this->router->generate('front_homepage');
        $request->getSession()->migrate();

        return new RedirectResponse($url);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\Security\Core\Exception\AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        if ($this->currentCustomer->findCurrentUser() !== null) {
            $domainId = $this->currentCustomer->findCurrentUser()->getDomainId();
            $locale = $this->domain->getDomainConfigById($domainId)->getLocale();

            $this->flashMessageSender->addErrorFlash(t('There was an error trying to log out. If you really want to sign out, please try it again.', [], 'messages', $locale));
        }

        $referer = $request->headers->get('referer');

        return new RedirectResponse($referer == null ? $this->router->generate('front_homepage') : $referer);
    }
}
