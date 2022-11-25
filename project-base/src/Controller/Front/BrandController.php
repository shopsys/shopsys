<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Symfony\Component\HttpFoundation\Response;

class BrandController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade $brandFacade
     */
    public function __construct(
        BrandFacade $brandFacade
    ) {
        $this->brandFacade = $brandFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('Front/Content/Brand/list.html.twig', [
            'brands' => $this->brandFacade->getAll(),
        ]);
    }
}
