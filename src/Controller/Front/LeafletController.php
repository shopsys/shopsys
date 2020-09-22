<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\TopCategory\TopCategoryFacade;
use Symfony\Component\HttpFoundation\Response;

class LeafletController extends FrontBaseController
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\Category\TopCategory\TopCategoryFacade
     */
    private TopCategoryFacade $topCategoryFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     */
    public function __construct(
        Domain $domain,
        TopCategoryFacade $topCategoryFacade
    ) {
        $this->domain = $domain;
        $this->topCategoryFacade = $topCategoryFacade;
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(): Response
    {
        return $this->render('Front/Content/Leaflet/index.html.twig', [
            'categories' => $this->topCategoryFacade->getVisibleCategoriesByDomainId($this->domain->getId()),
        ]);
    }
}
