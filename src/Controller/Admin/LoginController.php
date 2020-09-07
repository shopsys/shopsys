<?php

declare(strict_types=1);


namespace App\Controller\Admin;

use App\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouter;
use Shopsys\FrameworkBundle\Controller\Admin\LoginController as BaseLoginController;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @property \App\Component\Domain\Domain $domain
 * @method __construct(\Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator, \App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory, \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade $administratorLoginFacade)
 */
class LoginController extends BaseLoginController
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouter $domainRouter
     */
    private function changeDomainContext(DomainRouter $domainRouter)
    {
        $mainAdminDomainHost = $domainRouter->getContext()->getHost();
        $context = $this->container->get('router')->getContext();
        $context->setHost($mainAdminDomainHost);
    }

    /**
     * @Route("/", name="admin_login")
     * @Route("/login-check/", name="admin_login_check")
     * @Route("/logout/", name="admin_logout")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function loginAction(Request $request)
    {
        $currentDomainId = $this->domain->getId();
        if ($currentDomainId !== Domain::MAIN_ADMIN_DOMAIN_ID && !$this->isGranted(Roles::ROLE_ADMIN)) {
            $mainAdminDomainRouter = $this->domainRouterFactory->getRouter(Domain::MAIN_ADMIN_DOMAIN_ID);
            $this->changeDomainContext($mainAdminDomainRouter);

            $redirectTo = $this->generateUrl(
                'admin_login_sso',
                [
                    self::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $currentDomainId,
                    self::ORIGINAL_REFERER_PARAMETER_NAME => $request->server->get('HTTP_REFERER'),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->redirect($redirectTo);
        }

        return parent::loginAction($request);
    }

    /**
     * @Route("/sso/{originalDomainId}", requirements={"originalDomainId" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $originalDomainId
     */
    public function ssoAction(Request $request, $originalDomainId)
    {
        $administrator = $this->getUser();
        /* @var $administrator \App\Model\Administrator\Administrator */
        $multidomainToken = $this->administratorLoginFacade->generateMultidomainLoginTokenWithExpiration($administrator);
        $originalDomainRouter = $this->domainRouterFactory->getRouter((int)$originalDomainId);
        $this->changeDomainContext($originalDomainRouter);

        $redirectTo = $this->generateUrl(
            'admin_login_authorization',
            [
                self::MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME => $multidomainToken,
                self::ORIGINAL_REFERER_PARAMETER_NAME => $request->get(self::ORIGINAL_REFERER_PARAMETER_NAME),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return $this->redirect($redirectTo);
    }
}
