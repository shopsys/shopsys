<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Shopsys\Administration\Form\AdminLoginForm;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminLoginController extends AbstractController
{
    /**
     * @param \Symfony\Component\Security\Http\Authentication\AuthenticationUtils $authenticationUtils
     */
    public function __construct(
        protected readonly AuthenticationUtils $authenticationUtils,
    ) {
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route('/%admin_url%-new/login', name: 'sonata_admin_login')]
    public function loginAction(): Response
    {
        $form = $this->createForm(AdminLoginForm::class, [
            'email' => $this->authenticationUtils->getLastUsername(),
        ]);

        return $this->render('@ShopsysAdministration/security/login.html.twig', [
            'last_username' => $this->authenticationUtils->getLastUsername(),
            'form' => $form->createView(),
            'error' => $this->authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/%admin_url%-new/logout', name: 'sonata_admin_logout')]
    public function logoutAction(): void
    {
        // Left empty intentionally because this will be handled by Symfony.
    }
}
