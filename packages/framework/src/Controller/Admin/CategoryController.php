<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface $categoryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     * @param \Shopsys\FrameworkBundle\Component\Form\FormBuilderHelper $formBuilderHelper
     * @param \Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMixFacade $categorySeoMixFacade
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly CategoryDataFactoryInterface $categoryDataFactory,
        protected readonly Domain $domain,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly FormBuilderHelper $formBuilderHelper,
        protected readonly ReadyCategorySeoMixFacade $categorySeoMixFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/category/edit/{id}', requirements: ['id' => '\d+'])]
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

        return $this->render('@ShopsysFramework/Admin/Content/Category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/category/new/')]
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

        return $this->render('@ShopsysFramework/Admin/Content/Category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/category/list/')]
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

        return $this->render('@ShopsysFramework/Admin/Content/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($selectedDomainId === null),
            'domainFilterNamespace' => $domainFilterNamespace,
            'disabledFormFields' => $this->formBuilderHelper->hasFormDisabledFields(),
            'getAllCategoryIdsInSeoMixes' => $this->categorySeoMixFacade->getAllCategoryIdsInSeoMixes(),
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @see node_modules/@shopsys/framework/js/admin/components/CategoryTreeSorting.js
     */
    #[Route(path: '/category/apply-sorting/', methods: ['post'])]
    public function applySortingAction(Request $request): Response
    {
        $categoriesOrderingDataJson = $request->request->get('categoriesOrderingData');
        $categoriesOrderingData = Json::decode($categoriesOrderingDataJson, Json::FORCE_ARRAY);

        $this->categoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        return new Response('OK - dummy');
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/category/delete/{id}', requirements: ['id' => '\d+'])]
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

    /**
     * @param int $domainId
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/category/branch/{domainId}/{id}', requirements: ['domainId' => '\d+', 'id' => '\d+'], condition: 'request.isXmlHttpRequest()')]
    public function loadBranchJsonAction(int $domainId, int $id): Response
    {
        $parentCategory = $this->categoryFacade->getById($id);
        $categories = $parentCategory->getChildren();

        $categoriesData = [];

        foreach ($categories as $category) {
            $categoriesData[] = [
                'id' => $category->getId(),
                'categoryName' => $category->getName(),
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
}
