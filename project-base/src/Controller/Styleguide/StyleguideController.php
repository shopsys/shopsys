<?php

declare(strict_types=1);

namespace App\Controller\Styleguide;

use App\Controller\Front\FrontBaseController;

class StyleguideController extends FrontBaseController
{
    public function styleguideAction()
    {
        return $this->render('@ShopsysShop/Styleguide/styleguide.html.twig');
    }
}
