<?php

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Category\CategoryFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Category\CategoryFacade;
use Shopsys\FrameworkBundle\Model\Category\Exception\CategoryNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AdminBaseController
{
    protected const ALL_DOMAINS = 0;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryFacade $categoryFacade
     * @param \Shopsys\FrameworkBundle\Model\Category\CategoryDataFactoryInterface $categoryDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(
        protected readonly CategoryFacade $categoryFacade,
        protected readonly CategoryDataFactoryInterface $categoryDataFactory,
        protected readonly Domain $domain,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly RequestStack $requestStack,
    ) {
    }

    /**
     * @Route("/category/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                ]
            );
            return $this->redirectToRoute('admin_category_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(
            t('Editing category - %name%', ['%name%' => $category->getName()])
        );

        return $this->render('@ShopsysFramework/Admin/Content/Category/edit.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/category/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
                ]
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
            $categoriesWithPreloadedChildren = $this->categoryFacade->getAllCategoriesWithPreloadedChildren(
                $request->getLocale()
            );
        } else {
            $categoriesWithPreloadedChildren = $this->categoryFacade->getVisibleCategoriesWithPreloadedChildrenForDomain(
                $domainId,
                $request->getLocale()
            );
        }

        return $this->render('@ShopsysFramework/Admin/Content/Category/list.html.twig', [
            'categoriesWithPreloadedChildren' => $categoriesWithPreloadedChildren,
            'isForAllDomains' => ($domainId === static::ALL_DOMAINS),
        ]);
    }

    /**
     * @Route("/category/apply-sorting/", methods={"post"})
     * @see node_modules/@shopsys/framework/js/admin/components/CategoryTreeSorting.js
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function applySortingAction(Request $request): Response
    {
        $categoriesOrderingDataJson = $request->request->get('categoriesOrderingData');
        $categoriesOrderingData = Json::decode($categoriesOrderingDataJson, Json::FORCE_ARRAY);

        $this->categoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        return new Response('OK - dummy');
    }

    /**
     * @Route("/category/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->categoryFacade->getById($id)->getName();

            $this->categoryFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Category <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ]
            );
        } catch (CategoryNotFoundException $ex) {
            $this->addErrorFlash(t('Selected category doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_category_list');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listDomainTabsAction(): Response
    {
        $domainId = $this->requestStack->getSession()->get('categories_selected_domain_id', static::ALL_DOMAINS);

        return $this->render('@ShopsysFramework/Admin/Content/Category/domainTabs.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'selectedDomainId' => $domainId,
        ]);
    }

    /**
     * @Route("/category/branch/{domainId}/{id}", requirements={"domainId" = "\d+", "id" = "\d+"}, condition="request.isXmlHttpRequest()")
     * @param int $domainId
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
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
