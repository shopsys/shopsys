<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandController extends FrontBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        private readonly BrandFacade $brandFacade
    ) {
    }

    public function listAction()
    {
        return $this->render('Front/Content/Brand/list.html.twig', [
            'brands' => $this->brandFacade->getAll(),
        ]);
    }
}
