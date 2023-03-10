<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Component\Form\FormBuilderHelper;
use App\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Controller\Admin\CategoryController as BaseCategoryController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @property \App\Model\Category\CategoryDataFactory $categoryDataFactory
 * @property \App\Model\Category\CategoryFacade $categoryFacade
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
class CategoryController extends BaseCategoryController
{
    /**
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\Category\CategoryDataFactory $categoryDataFactory
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \App\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \App\Model\CategorySeo\ReadyCategorySeoMixFacade $categorySeoMixFacade
     */
    public function __construct(
        CategoryFacade $categoryFacade,
        CategoryDataFactoryInterface $categoryDataFactory,
        RequestStack $requestStack,
        Domain $domain,
        BreadcrumbOverrider $breadcrumbOverrider,
        private readonly FormBuilderHelper $formBuilderHelper,
        private readonly ReadyCategorySeoMixFacade $categorySeoMixFacade
    ) {
        parent::__construct(
            $categoryFacade,
            $categoryDataFactory,
            $domain,
            $breadcrumbOverrider,
            $requestStack
        );
    }

    /**
     * @Route("/category/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        if (count($this->domain->getAll()) > 1) {
            if ($request->query->has('domain')) {
                $domainId = (int)$request->query->get('domain');
            } else {
                $domainId = (int)$this->requestStack->getSession()->get('categories_selected_domain_id', static::ALL_DOMAINS);
            }
        } else {
            $domainId = static::ALL_DOMAINS;
        }

        if ($domainId !== static::ALL_DOMAINS) {
            try {
                $this->domain->getDomainConfigById($domainId);
            } catch (InvalidDomainIdException $ex) {
                $domainId = static::ALL_DOMAINS;
            }
        }

        $this->requestStack->getSession()->set('categories_selected_domain_id', $domainId);

        if ($domainId === static::ALL_DOMAINS) {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren($request->getLocale());
        } else {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain($domainId, $request->getLocale());
        }

        return $this->render('/Admin/Content/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($domainId === static::ALL_DOMAINS),
            'disabledFormFields' => $this->formBuilderHelper->hasFormDisabledFields(),
            'getAllCategoryIdsInSeoMixes' => $this->categorySeoMixFacade->getAllCategoryIdsInSeoMixes(),
        ]);
    }
}
