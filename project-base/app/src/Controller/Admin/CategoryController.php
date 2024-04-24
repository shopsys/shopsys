<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Controller\Admin\CategoryController as BaseCategoryController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Category\CategoryDataFactory $categoryDataFactory
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @method \App\Model\Administrator\Administrator getCurrentAdministrator()
 */
class CategoryController extends BaseCategoryController
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $categorySeoMixFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactoryInterface $categoryDataFactory,
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        private readonly FormBuilderHelper $formBuilderHelper,
        private readonly ReadyCategorySeoMixFacade $categorySeoMixFacade,
    ) {
        parent::__construct(
            $categoryFacade,
            $categoryDataFactory,
            $domain,
            $breadcrumbOverrider,
            $adminDomainFilterTabsFacade,
        );
    }

    /**
     * @Route("/category/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'category';
        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId === null) {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren(
                $request->getLocale(),
            );
        } else {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
                $selectedDomainId,
                $request->getLocale(),
            );
        }

        return $this->render('/Admin/Content/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($selectedDomainId === null),
            'domainFilterNamespace' => $domainFilterNamespace,
            'disabledFormFields' => $this->formBuilderHelper->hasFormDisabledFields(),
            'getAllCategoryIdsInSeoMixes' => $this->categorySeoMixFacade->getAllCategoryIdsInSeoMixes(),
        ]);
    }
}
