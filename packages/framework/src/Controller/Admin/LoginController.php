<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\Login\LoginFormType;
use Shopsys\FrameworkBundle\Model\Security\Exception\LoginWithDefaultPasswordException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\SecurityRequestAttributes;

class LoginController extends AdminBaseController
{
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
    ) {
    }

    #[Route(path: '/', name: 'admin_login')]
    #[Route(path: '/login-check/', name: 'admin_login_check')]
    #[Route(path: '/logout/', name: 'admin_logout')]
    public function loginAction(Request $request): Response
    {
        if ($this->isGranted(SystemRole::ADMIN)) {
            return $this->redirectToRoute('admin_default_dashboard');
        }

        $error = null;

        $form = $this->createForm(LoginFormType::class, null, [
            'action' => $this->generateUrl('admin_login_check'),
        ]);

        $lastAuthenticationError = $this->authenticationUtils->getLastAuthenticationError();

        if ($lastAuthenticationError !== null) {
            $error = match (true) {
                $lastAuthenticationError instanceof LoginWithDefaultPasswordException => t(
                    'Oh, you just tried to log in using default credentials. We do not allow that on production'
                    . ' environment. If you are random hacker, please go somewhere else. If you are authorized user,'
                    . ' please use another account or contact developers and change password during deployment.',
                ),
                $lastAuthenticationError instanceof TooManyLoginAttemptsAuthenticationException => t(
                    'Too many login attempts. Please try again later.',
                ),
                default => t('Log in failed.'),
            };
        }

        $lastUserName = $this->authenticationUtils->getLastUsername();
        $request->getSession()->remove(SecurityRequestAttributes::LAST_USERNAME);

        return $this->render('@ShopsysAdministration/content/login/loginForm.html.twig', [
            'form' => $form->createView(),
            'lastUsername' => $lastUserName,
            'error' => $error,
        ]);
    }
}
