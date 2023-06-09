<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;

class BrandController extends FrontBaseController
{
    private BrandFacade $brandFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        BrandFacade $brandFacade
    ) {
        $this->brandFacade = $brandFacade;
    }

    public function listAction()
    {
        return $this->render('Front/Content/Brand/list.html.twig', [
            'brands' => $this->brandFacade->getAll(),
        ]);
    }
}
