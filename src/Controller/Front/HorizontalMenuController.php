<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Model\Category\CategoryFacade;
use App\Model\HorizontalMenu\HorizontalMenuItemFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Symfony\Component\HttpFoundation\Response;

class HorizontalMenuController extends FrontBaseController
{
    /**
     * @var \App\Model\HorizontalMenu\HorizontalMenuItemFacade
     */
    private $horizontalMenuItemFacade;

    /**
     * @var \App\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var CategoryFacade $categoryFacade
     */
    private $categoryFacade;

    /**
     * @param \App\Model\HorizontalMenu\HorizontalMenuItemFacade $horizontalMenuItemFacade
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        HorizontalMenuItemFacade $horizontalMenuItemFacade,
        Domain $domain,
        CategoryFacade $categoryFacade
    ) {
        $this->horizontalMenuItemFacade = $horizontalMenuItemFacade;
        $this->domain = $domain;
        $this->categoryFacade = $categoryFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function menuAction(): Response
    {
        $domainId = $this->domain->getId();
        $itemDetails = $this->horizontalMenuItemFacade->getOrderedHorizontalMenuItemDetails($domainId);
        $countOfRecentlyBoughtProducts = 0;

        return $this->render('Front/Inline/HorizontalMenu/menu.html.twig', [
            'itemDetails' => $itemDetails,
            'countOfRecentlyBoughtProducts' => $countOfRecentlyBoughtProducts,
            'saleCategory' => $this->categoryFacade->findSaleCategory(),
        ]);
    }
}
