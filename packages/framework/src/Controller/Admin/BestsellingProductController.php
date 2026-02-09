<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\BestsellingProduct\BestsellingProductFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Product\BestsellingProduct\ManualBestsellingProductFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_BESTSELLING_PRODUCT)]
class BestsellingProductController extends AdminBaseController
{
    public function __construct(
        protected readonly ManualBestsellingProductFacade $manualBestsellingProductFacade,
        protected readonly CategoryFacade $categoryFacade,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly Localization $localization,
    ) {
    }

    #[Route(path: '/product/bestselling-product/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
            $domainId,
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );

        $bestsellingProductsInCategories = $this->manualBestsellingProductFacade->getCountsIndexedByCategoryId(
            $domainId,
        );

        return $this->render('@ShopsysAdministration/content/bestsellingProduct/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'selectedDomainId' => $domainId,
            'bestsellingProductsInCategories' => $bestsellingProductsInCategories,
        ]);
    }

    #[Route(path: '/product/bestselling-product/detail/')]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function detailAction(Request $request): Response
    {
        $categoryId = $request->query->getInt('categoryId');
        $category = $this->categoryFacade->getById($categoryId);
        $domainId = $request->query->getInt('domainId');

        $products = $this->manualBestsellingProductFacade->getProductsIndexedByPosition(
            $category,
            $domainId,
        );

        $form = $this->createForm(BestsellingProductFormType::class, $products);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newProducts = $form->getData();

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

        return $this->render('@ShopsysAdministration/content/bestsellingProduct/detail.html.twig', [
            'form' => $form->createView(),
            'categoryName' => $category->getName(),
        ]);
    }
}
