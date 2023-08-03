<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Form\Admin\BlogCategoryFormType;
use App\Model\Blog\Category\BlogCategoryDataFactory;
use App\Model\Blog\Category\BlogCategoryFacade;
use App\Model\Blog\Category\Exception\BlogCategoryNotFoundException;
use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Domain\Exception\InvalidDomainIdException;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Controller\Admin\AdminBaseController;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class BlogCategoryController extends AdminBaseController
{
    private const ALL_DOMAINS = 0;
    private const SESSION_BLOG_CATEGORIES_SELECTED_DOMAIN_ID = 'blog_categories_selected_domain_id';

    /**
     * @param \App\Model\Blog\Category\BlogCategoryFacade $blogCategoryFacade
     * @param \App\Model\Blog\Category\BlogCategoryDataFactory $blogCategoryDataFactory
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     */
    public function __construct(
        private BlogCategoryFacade $blogCategoryFacade,
        private BlogCategoryDataFactory $blogCategoryDataFactory,
        private SessionInterface $session,
        private Domain $domain,
        private BreadcrumbOverrider $breadcrumbOverrider,
    ) {
    }

    /**
     * @Route("/blog/category/edit/{id}", requirements={"id" = "\d+"}, name="admin_blogcategory_edit")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
    {
        $blogCategory = $this->blogCategoryFacade->getById($id);
        $blogCategoryData = $this->blogCategoryDataFactory->createFromBlogCategory($blogCategory);

        $form = $this->createForm(BlogCategoryFormType::class, $blogCategoryData, [
            'blogCategory' => $blogCategory,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->blogCategoryFacade->edit($id, $blogCategoryData);

            $this->addSuccessFlashTwig(
                t('Blog category <strong><a href="{{ url }}">{{ name }}</a></strong> has been updated'),
                [
                    'name' => $blogCategory->getName(),
                    'url' => $this->generateUrl('admin_blogcategory_edit', ['id' => $blogCategory->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_blogcategory_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing blog category - %name%', ['%name%' => $blogCategory->getName()]));

        return $this->render('Admin/Content/Blog/Category/edit.html.twig', [
            'form' => $form->createView(),
            'blogCategory' => $blogCategory,
        ]);
    }

    /**
     * @Route("/blog/category/new/", name="admin_blogcategory_new")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
    {
        $blogCategoryData = $this->blogCategoryDataFactory->create();

        $form = $this->createForm(BlogCategoryFormType::class, $blogCategoryData, [
            'blogCategory' => null,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blogCategory = $this->blogCategoryFacade->create($blogCategoryData);

            $this->addSuccessFlashTwig(
                t('Blog category <strong><a href="{{ url }}">{{ name }}</a></strong> has been created'),
                [
                    'name' => $blogCategory->getName(),
                    'url' => $this->generateUrl('admin_blogcategory_edit', ['id' => $blogCategory->getId()]),
                ],
            );

            return $this->redirectToRoute('admin_blogcategory_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('Admin/Content/Blog/Category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/blog/category/list/", name="admin_blogcategory_list")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request): Response
    {
        if (count($this->domain->getAll()) > 1) {
            if ($request->query->has('domain')) {
                $domainId = (int)$request->query->get('domain');
            } else {
                $domainId = (int)$this->session->get(self::SESSION_BLOG_CATEGORIES_SELECTED_DOMAIN_ID, self::ALL_DOMAINS);
            }
        } else {
            $domainId = self::ALL_DOMAINS;
        }

        if ($domainId !== self::ALL_DOMAINS) {
            try {
                $this->domain->getDomainConfigById($domainId);
            } catch (InvalidDomainIdException $ex) {
                $domainId = self::ALL_DOMAINS;
            }
        }

        $this->session->set(self::SESSION_BLOG_CATEGORIES_SELECTED_DOMAIN_ID, $domainId);

        if ($domainId === self::ALL_DOMAINS) {
            $blogCategoriesWithPreloadedChildren = $this->blogCategoryFacade->getAllBlogCategoriesWithPreloadedChildren($request->getLocale());
        } else {
            $blogCategoriesWithPreloadedChildren = $this->blogCategoryFacade->getVisibleBlogCategoriesWithPreloadedChildrenOnDomain($domainId, $request->getLocale());
        }

        return $this->render('Admin/Content/Blog/Category/list.html.twig', [
            'blogCategoriesWithPreloadedChildren' => $blogCategoriesWithPreloadedChildren,
            'isForAllDomains' => ($domainId === self::ALL_DOMAINS),
        ]);
    }

    /**
     * @Route("/blog/category/apply-sorting/", methods={"post"}, condition="request.isXmlHttpRequest()")
     * @see node_modules/@shopsys/framework/js/admin/components/CategoryTreeSorting.js
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function applySortingAction(Request $request): Response
    {
        $categoriesOrderingDataJson = $request->request->get('categoriesOrderingData');
        $categoriesOrderingData = Json::decode($categoriesOrderingDataJson, Json::FORCE_ARRAY);

        $this->blogCategoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        return new Response('OK - dummy');
    }

    /**
     * @Route("/blog/category/delete/{id}", requirements={"id" = "\d+"}, name="admin_blogcategory_delete")
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->blogCategoryFacade->getById($id)->getName();

            $this->blogCategoryFacade->deleteById($id);

            $this->addSuccessFlashTwig(
                t('Blog category <strong>{{ name }}</strong> has been removed'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (BlogCategoryNotFoundException $ex) {
            $this->addErrorFlash(t('Selected blog category does not exist.'));
        }

        return $this->redirectToRoute('admin_blogcategory_list');
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listDomainTabsAction(): Response
    {
        $domainId = $this->session->get(self::SESSION_BLOG_CATEGORIES_SELECTED_DOMAIN_ID, self::ALL_DOMAINS);

        return $this->render('Admin/Content/Blog/Category/domainTabs.html.twig', [
            'domainConfigs' => $this->domain->getAll(),
            'selectedDomainId' => $domainId,
        ]);
    }

    /**
     * @Route("/blog/category/branch/{domainId}/{id}", requirements={"domainId" = "\d+", "id" = "\d+"}, condition="request.isXmlHttpRequest()", name="admin_blogcategory_loadbranchjson")
     * @param int $domainId
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function loadBranchJsonAction(int $domainId, int $id): JsonResponse
    {
        $blogParentCategory = $this->blogCategoryFacade->getById($id);
        $blogCategories = $blogParentCategory->getChildren();

        $blogCategoriesData = [];

        foreach ($blogCategories as $blogCategory) {
            $blogCategoriesData[] = [
                'id' => $blogCategory->getId(),
                'categoryName' => $blogCategory->getName(),
                'isVisible' => $blogCategory->isVisible($domainId),
                'hasChildren' => $blogCategory->hasChildren(),
                'loadUrl' => $this->generateUrl('admin_blogcategory_loadbranchjson', [
                    'domainId' => $domainId,
                    'id' => $blogCategory->getId(),
                ]),
            ];
        }

        return $this->json($blogCategoriesData);
    }
}
