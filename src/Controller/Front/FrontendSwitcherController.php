<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class FrontendSwitcherController extends FrontBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        $response = new RedirectResponse('/');
        $response->headers->clearCookie('twigStorefront');

        return $response;
    }
}
