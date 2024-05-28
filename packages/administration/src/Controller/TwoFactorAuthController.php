<?php

declare(strict_types=1);

namespace Shopsys\Administration\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TwoFactorAuthController extends AbstractController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/%admin_url%-new/two-factor-auth-enable', name: 'two_factor_auth_enable')]
    public function twoFactorAuthEnableAction(): Response
    {
        return $this->render('@ShopsysAdministration/security/2fa_enable.html.twig');
    }
}
