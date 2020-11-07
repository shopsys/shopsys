<?php

declare(strict_types=1);

namespace App\Controller\Front;

use App\Component\Domain\Domain;
use App\Model\Category\Category;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Symfony\Component\HttpFoundation\Response;

class CategorySeoMixController extends FrontBaseController
{
    /**
     * @var \App\Component\Domain\Domain
     */
    private Domain $domain;

    /**
     * @var \App\Model\CategorySeo\ReadyCategorySeoMixFacade
     */
    private ReadyCategorySeoMixFacade $readyCategorySeoMixFacade;

    /**
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
     */
    public function __construct(
        Domain $domain,
        ReadyCategorySeoMixFacade $readyCategorySeoMixFacade
    ) {
        $this->domain = $domain;
        $this->readyCategorySeoMixFacade = $readyCategorySeoMixFacade;
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param int|null $selectedReadyCategorySeoMixId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function childSeoCategoryMixesAction(Category $category, ?int $selectedReadyCategorySeoMixId): Response
    {
        $readyCategorySeoMixes = $this->readyCategorySeoMixFacade->getAllForShowInCategory($category, $this->domain->getId());

        return $this->render('Front/Content/CategorySeoMix/childSeoCategoryMixes.html.twig', [
            'readyCategorySeoMixes' => $readyCategorySeoMixes,
            'selectedReadyCategorySeoMixId' => $selectedReadyCategorySeoMixId,
        ]);
    }
}
