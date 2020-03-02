<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\CategorySeoFilterFormType;
use App\Model\Category\Category;
use App\Model\Category\CategoryFacade;
use App\Model\CategorySeo\CategorySeoFacade;
use App\Model\CategorySeo\CategorySeoFiltersData;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategorySeoController extends AdminBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private $adminDomainTabsFacade;

    /**
     * @var \App\Model\Category\CategoryFacade
     */
    private $categoryFacade;

    /**
     * @var \App\Model\CategorySeo\CategorySeoFacade
     */
    private $categorySeoFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \App\Model\Category\CategoryFacade $categoryFacade
     * @param \App\Model\CategorySeo\CategorySeoFacade $categorySeoFacade
     */
    public function __construct(
        AdminDomainTabsFacade $adminDomainTabsFacade,
        CategoryFacade $categoryFacade,
        CategorySeoFacade $categorySeoFacade
    ) {
        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
        $this->categoryFacade = $categoryFacade;
        $this->categorySeoFacade = $categorySeoFacade;
    }

    /**
     * @Route("/seo/category/", name="admin_categoryseo_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): Response
    {
        return $this->render('Admin/Content/CategorySeo/list.html.twig');
    }

    /**
     * @Route("/seo/category/new/category", name="admin_categoryseo_newcategory")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCategoryAction(): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $locale
        );

        return $this->render('Admin/Content/CategorySeo/newCategory.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/seo/category/new/filters/category/{categoryId}", requirements={"categoryId" = "\d+"}, name="admin_categoryseo_newfilters")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newFiltersAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);
        $categorySeoFiltersData = new CategorySeoFiltersData();

        $form = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $request->get('is_for_backlink', false) === false) {
            return $this->redirect(
                $this->getUrlWithCategoryIdAndAllQueryParameters(
                    'admin_categoryseo_newcombinations',
                    $categoryId,
                    $request,
                    false
                )
            );
        }

        return $this->render('Admin/Content/CategorySeo/newFilters.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'locale' => $locale,
        ]);
    }

    /**
     * @Route("/seo/category/new/combinations/category/{categoryId}", requirements={"categoryId" = "\d+"}, name="admin_categoryseo_newcombinations")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $categoryId
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newCombinationsAction(Request $request, int $categoryId): Response
    {
        $locale = $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale();

        $category = $this->categoryFacade->getById($categoryId);
        $categorySeoFiltersData = new CategorySeoFiltersData();

        $form = $this->createCategorySeoFilterForm($category, $categorySeoFiltersData);
        $form->handleRequest($request);

        $categorySeoMixes = $this->categorySeoFacade->getCategorySeoMixes(
            $category,
            $categorySeoFiltersData,
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $this->adminDomainTabsFacade->getSelectedDomainConfig()->getLocale()
        );

        return $this->render('Admin/Content/CategorySeo/newCombinations.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
            'categorySeoMixes' => $categorySeoMixes,
            'categorySeoFiltersData' => $categorySeoFiltersData,
            'locale' => $locale,
            'backLink' => $this->getUrlWithCategoryIdAndAllQueryParameters(
                'admin_categoryseo_newfilters',
                $categoryId,
                $request,
                true
            ),
        ]);
    }

    /**
     * @param \App\Model\Category\Category $category
     * @param \App\Model\CategorySeo\CategorySeoFiltersData $categorySeoFiltersData
     * @return \Symfony\Component\Form\FormInterface
     */
    private function createCategorySeoFilterForm(
        Category $category,
        CategorySeoFiltersData $categorySeoFiltersData
    ): FormInterface {
        return $this->createForm(CategorySeoFilterFormType::class, $categorySeoFiltersData, [
            'category' => $category,
            'domainId' => $this->adminDomainTabsFacade->getSelectedDomainId(),
            'method' => 'GET',
        ]);
    }

    /**
     * @param string $routeName
     * @param int  $categoryId
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param bool $isForBackLink
     * @return string
     */
    private function getUrlWithCategoryIdAndAllQueryParameters(
        string $routeName,
        int $categoryId,
        Request $request,
        bool $isForBackLink
    ): string {
        return $this->generateUrl(
            $routeName,
            array_merge(
                ['categoryId' => $categoryId],
                $request->query->all(),
                ['is_for_backlink' => $isForBackLink]
            )
        );
    }
}
