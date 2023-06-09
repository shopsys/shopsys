<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Form\Admin\Login\LoginFormType;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade;
use Shopsys\FrameworkBundle\Model\Security\Authenticator;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginFailedException;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;

class LoginController extends AdminBaseController
{
    protected const MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME = 'multidomainLoginToken';
    public const ORIGINAL_DOMAIN_ID_PARAMETER_NAME = 'originalDomainId';
    public const ORIGINAL_REFERER_PARAMETER_NAME = 'originalReferer';

    /**
     * @param \Shopsys\FrameworkBundle\Model\Security\Authenticator $authenticator
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade $administratorLoginFacade
     */
    public function __construct(
        protected readonly Authenticator $authenticator,
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly AdministratorLoginFacade $administratorLoginFacade,
    ) {
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
            $redirectTo = $mainAdminDomainRouter->generate(
                'admin_login_sso',
                [
                    self::ORIGINAL_DOMAIN_ID_PARAMETER_NAME => $currentDomainId,
                    self::ORIGINAL_REFERER_PARAMETER_NAME => $request->server->get('HTTP_REFERER'),
                ],
                UrlGeneratorInterface::ABSOLUTE_URL,
            );

            return $this->redirect($redirectTo);
        }
        if ($this->isGranted(Roles::ROLE_ADMIN)) {
            return $this->redirectToRoute('admin_default_dashboard');
        }

        $error = null;

        $form = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('admin_login_check'),
        ]);

        try {
            $this->authenticator->checkLoginProcess($request);
        } catch (LoginFailedException $e) {
            if ($e->getPrevious() instanceof LoginWithDefaultPasswordException) {
                $error = t(
                    'Oh, you just tried to log in using default credentials. We do not allow that on production'
                        . ' environment. If you are random hacker, please go somewhere else. If you are authorized user,'
                        . ' please use another account or contact developers and change password during deployment.',
                );
            } elseif ($e->getPrevious() instanceof TooManyLoginAttemptsAuthenticationException) {
                $error = t('Too many login attempts. Please try again later.');
            } else {
                $error = t('Log in failed.');
            }
        }

        return $this->render('@ShopsysFramework/Admin/Content/Login/loginForm.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
        ]);
    }

    /**
     * @Route("/sso/{originalDomainId}", requirements={"originalDomainId" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $originalDomainId
     */
    public function ssoAction(Request $request, $originalDomainId)
    {
        /** @var \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator */
        $administrator = $this->getUser();
        $multidomainToken = $this->administratorLoginFacade->generateMultidomainLoginTokenWithExpiration(
            $administrator,
        );
        $originalDomainRouter = $this->domainRouterFactory->getRouter((int)$originalDomainId);
        $redirectTo = $originalDomainRouter->generate(
            'admin_login_authorization',
            [
                static::MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME => $multidomainToken,
                self::ORIGINAL_REFERER_PARAMETER_NAME => $request->get(self::ORIGINAL_REFERER_PARAMETER_NAME),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return $this->redirect($redirectTo);
    }

    /**
     * @Route("/authorization/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function authorizationAction(Request $request)
    {
        $multidomainLoginToken = $request->get(static::MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME);
        $originalReferer = $request->get(self::ORIGINAL_REFERER_PARAMETER_NAME);
        try {
            $this->administratorLoginFacade->loginByMultidomainToken($request, $multidomainLoginToken);
        } catch (InvalidTokenException $ex) {
            return $this->render('@ShopsysFramework/Admin/Content/Login/loginFailed.html.twig');
        }
        $redirectTo = $originalReferer !== null ? $originalReferer : $this->generateUrl('admin_default_dashboard');

        return $this->redirect($redirectTo);
    }
}
