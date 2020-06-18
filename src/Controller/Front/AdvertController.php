<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Symfony\Component\HttpFoundation\Response;

class AdvertController extends FrontBaseController
{
    /**
     * @var \App\Model\Advert\AdvertFacade
     */
    private $advertFacade;

    /**
     * @param \App\Model\Advert\AdvertFacade $advertFacade
     */
    public function __construct(AdvertFacade $advertFacade)
    {
        $this->advertFacade = $advertFacade;
    }

    /**
     * @param string $positionName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function boxAction(string $positionName): Response
    {
        $advert = $this->advertFacade->findRandomAdvertByPositionOnCurrentDomain($positionName);

        return $this->render('Front/Content/Advert/box.html.twig', [
            'advert' => $advert,
        ]);
    }

    /**
     * @param string $positionName
     * @param \App\Model\Category\Category $category
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function categoryBoxAction(string $positionName, Category $category): Response
    {
        $advert = $this->advertFacade->findRandomAdvertByPositionAndCategoryOnCurrentDomain($positionName, $category);

        return $this->render('Front/Content/Advert/box.html.twig', [
            'advert' => $advert,
        ]);
    }
}
