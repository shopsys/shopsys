<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_CATEGORY)]
class CategoryController extends AdminBaseController
{
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly CategoryDataFactory $categoryDataFactory,
        protected readonly Domain $domain,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly FormBuilderHelper $formBuilderHelper,
        protected readonly ReadyCategorySeoMixFacade $categorySeoMixFacade,
        protected readonly Localization $localization,
    ) {
    }

    #[Route(path: '/category/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $category = $this->categoryFacade->getById($id);
        $categoryData = $this->categoryDataFactory->createFromCategory($category);

        $form = $this->createForm(CategoryFormType::class, $categoryData, [
            'category' => $category,
            'scenario' => CategoryFormType::SCENARIO_EDIT,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryFacade->edit($id, $categoryData);

            $this->addSuccessFlashTwig(
                t('Category <strong><a href="{{ url }}">{{ name }}</a></strong> was modified'),
                [
                    'name' => $category->getName(),
                    'url' => $this->generateUrl('admin_category_edit', ['id' => $category->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_category_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing category - %name%', ['%name%' => $category->getName()]),
        );

        return $this->render('@ShopsysAdministration/content/category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    #[Route(path: '/category/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $categoryData = $this->categoryDataFactory->create();

        $form = $this->createForm(CategoryFormType::class, $categoryData, [
            'category' => null,
            'scenario' => CategoryFormType::SCENARIO_CREATE,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category = $this->categoryFacade->create($categoryData);

            $this->addSuccessFlashTwig(
                t('Category <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                [
                    'name' => $category->getName(),
                    'url' => $this->generateUrl('admin_category_edit', ['id' => $category->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_category_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/category/list/')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'category';
        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);
        $visibilityOfCategoriesIndexedById = null;

        if ($selectedDomainId === null) {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren(
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );
            $visibilityOfCategoriesIndexedById = $this->getVisibleCategoryIdsForAllDomains();
        } else {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
                $selectedDomainId,
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );
        }

        return $this->render('@ShopsysAdministration/content/category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($selectedDomainId === null),
            'domainFilterNamespace' => $domainFilterNamespace,
            'disabledFormFields' => $this->formBuilderHelper->hasFormDisabledFields(),
            'allCategoryIdsInSeoMixes' => $this->categorySeoMixFacade->getAllCategoryIdsInSeoMixes(),
            'visibilityOfCategoriesIndexedById' => $visibilityOfCategoriesIndexedById,
        ]);
    }

    /**
     * @see node_modules/@shopsys/framework/js/admin/components/CategoryTreeSorting.js
     */
    #[Route(path: '/category/apply-sorting/', methods: ['post'])]
    #[CanEdit]
    public function applySortingAction(Request $request): Response
    {
        $categoriesOrderingDataJson = $request->request->get('categoriesOrderingData');
        $categoriesOrderingData = Json::decode($categoriesOrderingDataJson, true);

        $this->categoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        return new Response('OK - dummy');
    }

    #[Route(path: '/category/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->categoryFacade->getById($id)->getName();

            $this->categoryFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Category <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (CategoryNotFoundException $ex) {
            $this->addErrorFlash(t('Selected category doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_category_list');
    }

    #[Route(path: '/category/branch/{domainId}/{id}', requirements: ['domainId' => '\d+', 'id' => '\d+'], condition: 'request.isXmlHttpRequest()')]
    #[CanView]
    public function loadBranchJsonAction(int $domainId, int $id): Response
    {
        $parentCategory = $this->categoryFacade->getById($id);
        $categories = $parentCategory->getChildren();

        $categoriesData = [];

        foreach ($categories as $category) {
            $categoriesData[] = [
                'id' => $category->getId(),
                'label' => $category->getName(),
                'isVisible' => $category->isVisible($domainId),
                'hasChildren' => $category->hasChildren(),
                'loadUrl' => $this->generateUrl('admin_category_loadbranchjson', [
                    'domainId' => $domainId,
                    'id' => $category->getId(),
                ]),
            ];
        }

        return $this->json($categoriesData);
    }

    /**
     * @return array<int, string>
     */
    protected function getVisibleCategoryIdsForAllDomains(): array
    {
        $domainsCount = count($this->domain->getAll());

        return $this->categoryFacade->getVisibilityOfCategoriesIndexedById($domainsCount);
    }
}
