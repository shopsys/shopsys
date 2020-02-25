<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AccessController extends AdminBaseController
{
    /**
     * @Route("/access-denied/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deniedAction(Request $request): Response
    {
        $this->addErrorFlash(
            t('You are not allowed to access the requested page. Please ask your administrator to grant you access to the requested page.')
        );
        return new RedirectResponse($this->generateUrl('admin_default_dashboard'));
    }
}
