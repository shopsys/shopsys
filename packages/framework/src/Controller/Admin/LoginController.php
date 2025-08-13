<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Form\Admin\Login\LoginFormType;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Shopsys\FrameworkBundle\Model\Security\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginController extends AdminBaseController
{
    /**
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     */
    public function __construct(
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
}
