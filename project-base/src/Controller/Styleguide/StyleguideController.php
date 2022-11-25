<?php

declare(strict_types=1);

namespace App\Controller\Styleguide;

use App\Controller\Front\FrontBaseController;
use Symfony\Component\HttpFoundation\Response;

class StyleguideController extends FrontBaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function styleguideAction(): Response
    {
        return $this->render('Styleguide/styleguide.html.twig');
    }
}
