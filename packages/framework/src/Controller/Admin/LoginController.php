<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory;
use Shopsys\FrameworkBundle\Form\Admin\Login\LoginFormType;
use Shopsys\FrameworkBundle\Model\Administrator\Security\Exception\InvalidTokenException;
use Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginController extends AdminBaseController
{
    protected const string MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME = 'multidomainLoginToken';
    public const string ORIGINAL_DOMAIN_ID_PARAMETER_NAME = 'originalDomainId';
    public const string ORIGINAL_REFERER_PARAMETER_NAME = 'originalReferer';

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\DomainRouterFactory $domainRouterFactory
     * @param \Shopsys\FrameworkBundle\Model\Security\AdministratorLoginFacade $administratorLoginFacade
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly DomainRouterFactory $domainRouterFactory,
        protected readonly AdministratorLoginFacade $administratorLoginFacade,
        protected readonly AuthenticationUtils $authenticationUtils,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/', name: 'admin_login')]
    #[Route(path: '/login-check/', name: 'admin_login_check')]
    #[Route(path: '/logout/', name: 'admin_logout')]
    public function loginAction(Request $request): Response
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

        $lastAuthenticationError = $this->authenticationUtils->getLastAuthenticationError();

        if ($lastAuthenticationError !== null) {
            $error = match (true) {
                $lastAuthenticationError->getPrevious() instanceof LoginWithDefaultPasswordException => t(
                    'Oh, you just tried to log in using default credentials. We do not allow that on production'
                    . ' environment. If you are random hacker, please go somewhere else. If you are authorized user,'
                    . ' please use another account or contact developers and change password during deployment.',
                ),
                $lastAuthenticationError->getPrevious() instanceof TooManyLoginAttemptsAuthenticationException => t(
                    'Too many login attempts. Please try again later.',
                ),
                default => t('Log in failed.'),
            };
        }

        $lastUserName = $this->authenticationUtils->getLastUsername();
        $request->getSession()->remove(SecurityRequestAttributes::LAST_USERNAME);

        return $this->render('@ShopsysFramework/Admin/Content/Login/loginForm.html.twig', [
            'form' => $form->createView(),
            'lastUsername' => $lastUserName,
            'error' => $error,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $originalDomainId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/sso/{originalDomainId}', requirements: ['originalDomainId' => '\d+'])]
    public function ssoAction(Request $request, int $originalDomainId): Response
    {
        $multidomainToken = $this->administratorLoginFacade->generateMultidomainLoginTokenWithExpiration(
            $this->getCurrentAdministrator(),
        );
        $originalDomainRouter = $this->domainRouterFactory->getRouter($originalDomainId);
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/authorization/')]
    public function authorizationAction(Request $request): Response
    {
        $multidomainLoginToken = $request->get(static::MULTIDOMAIN_LOGIN_TOKEN_PARAMETER_NAME);
        $originalReferer = $request->get(self::ORIGINAL_REFERER_PARAMETER_NAME);

        try {
            $this->administratorLoginFacade->loginByMultidomainToken($request, $multidomainLoginToken);
        } catch (InvalidTokenException) {
            return $this->render('@ShopsysFramework/Admin/Content/Login/loginFailed.html.twig');
        }
        $redirectTo = $originalReferer ?? $this->generateUrl('admin_default_dashboard');

        return $this->redirect($redirectTo);
    }
}
