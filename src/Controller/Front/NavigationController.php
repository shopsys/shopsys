<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Category\CategoryFacade;
use App\Model\Navigation\NavigationItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class NavigationController extends FrontBaseController
{
    /**
     * @var \App\Model\Navigation\NavigationItemFacade
     */
    private NavigationItemFacade $navigationItemFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private CategoryFacade $categoryFacade;

    /**
     * @param \App\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     */
    public function __construct(
        NavigationItemFacade $navigationItemFacade,
        Domain $domain,
        CategoryFacade $categoryFacade
    ) {
        $this->navigationItemFacade = $navigationItemFacade;
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        $domainId = $this->domain->getId();
        $itemDetails = $this->navigationItemFacade->getOrderedNavigationItemDetails($domainId);
        $countOfRecentlyBoughtProducts = 0;

        return $this->render('Front/Inline/Navigation/menu.html.twig', [
            'itemDetails' => $itemDetails,
            'countOfRecentlyBoughtProducts' => $countOfRecentlyBoughtProducts,
            'saleCategory' => $this->categoryFacade->findSaleCategory(),
        ]);
    }
}
