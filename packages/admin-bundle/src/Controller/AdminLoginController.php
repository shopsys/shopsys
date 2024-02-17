<?php

declare(strict_types=1);

namespace Shopsys\AdminBundle\Controller;

use Shopsys\AdminBundle\Form\AdminLoginForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminLoginController extends AbstractController
{
    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils
    ) {
    }

    #[Route('/admin-new/login', name: 'sonata_admin_login')]
    public function loginAction(): Response
    {
        $form = $this->createForm(AdminLoginForm::class, [
            'email' => $this->authenticationUtils->getLastUsername()
        ]);

        return $this->render('@ShopsysAdmin/security/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'form' => $form->createView(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/admin-new/logout', name: 'sonata_admin_logout')]
    public function logoutAction(): void
    {
        // Left empty intentionally because this will be handled by Symfony.
    }
}