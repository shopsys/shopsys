<?php

declare(strict_types=1);

namespace App\Controller\Styleguide;

use App\Controller\Front\FrontBaseController;

class StyleguideController extends FrontBaseController
{
    public function styleguideAction(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render('Styleguide/styleguide.html.twig');
    }
}
