<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Controller\Styleguide;

use Shopsys\ShopBundle\Controller\Front\FrontBaseController;

class StyleguideController extends FrontBaseController
{
    public function styleguideAction()
    {
        return $this->render('@ShopsysShop/Styleguide/styleguide.html.twig');
    }
}
