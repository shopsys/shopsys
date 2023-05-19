<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Form\Admin\BestsellingProduct\BestsellingProductFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class BestsellingProductController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade $manualBestsellingProductFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        protected readonly ManualBestsellingProductFacade $manualBestsellingProductFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/product/bestselling-product/list/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function listAction(Request $request)
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
            $domainId,
            $request->getLocale(),
        );

        $bestsellingProductsInCategories = $this->manualBestsellingProductFacade->getCountsIndexedByCategoryId(
            $domainId,
        );

        return $this->render('@ShopsysFramework/Admin/Content/BestsellingProduct/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'selectedDomainId' => $domainId,
            'bestsellingProductsInCategories' => $bestsellingProductsInCategories,
        ]);
    }

    /**
     * @Route("/product/bestselling-product/detail/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function detailAction(Request $request)
    {
        $category = $this->categoryFacade->getById($request->get('categoryId'));
        $domainId = $request->get('domainId');

        $products = $this->manualBestsellingProductFacade->getProductsIndexedByPosition(
            $category,
            $domainId,
        );

        $form = $this->createForm(BestsellingProductFormType::class, ['products' => $products]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newProducts = $form->getData()['products'];

            $this->manualBestsellingProductFacade->edit($category, $domainId, $newProducts);

            $this
                ->addSuccessFlashTwig(
                    t('Best-selling products of category <strong><a href="{{ url }}">{{ name }}</a></strong> set.'),
                    [
                        'name' => $category->getName(),
                        'url' => $this->generateUrl(
                            'admin_bestsellingproduct_detail',
                            ['domainId' => $domainId, 'categoryId' => $category->getId()],
                        ),
                    ],
                );

            return $this->redirectToRoute('admin_bestsellingproduct_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem($category->getName());

        return $this->render('@ShopsysFramework/Admin/Content/BestsellingProduct/detail.html.twig', [
            'form' => $form->createView(),
            'categoryName' => $category->getName(),
        ]);
    }
}
