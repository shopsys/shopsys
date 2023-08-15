<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;

class AdvertController extends FrontBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertFacade $advertFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        private readonly AdvertFacade $advertFacade,
        private readonly CategoryFacade $categoryFacade,
    ) {
    }

    /**
     * @param string $positionName
     * @param int|null $categoryId
     */
    public function boxAction($positionName, $categoryId = null)
    {
        $category = null;

        if ($categoryId !== null) {
            $category = $this->categoryFacade->getById($categoryId);
        }

        $advert = $this->advertFacade->findRandomAdvertByPositionOnCurrentDomain($positionName, $category);

        return $this->render('Front/Content/Advert/box.html.twig', [
            'advert' => $advert,
        ]);
    }
}
