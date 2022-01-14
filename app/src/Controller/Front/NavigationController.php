<?php

declare(strict_types=1);

namespace App\Controller\Front;

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
     * @param \App\Model\Navigation\NavigationItemFacade $navigationItemFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        NavigationItemFacade $navigationItemFacade,
        Domain $domain
    ) {
        $this->navigationItemFacade = $navigationItemFacade;
        $this->domain = $domain;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        $itemDetails = $this->navigationItemFacade->getOrderedNavigationItemDetails($this->domain->getCurrentDomainConfig());
        $countOfRecentlyBoughtProducts = 0;

        return $this->render('Front/Inline/Navigation/menu.html.twig', [
            'itemDetails' => $itemDetails,
            'countOfRecentlyBoughtProducts' => $countOfRecentlyBoughtProducts,
        ]);
    }
}
