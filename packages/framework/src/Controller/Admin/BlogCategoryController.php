<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Nette\Utils\Json;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Blog\BlogCategoryFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Category\BlogCategoryFacade;
use Shopsys\FrameworkBundle\Model\Blog\Category\Exception\BlogCategoryNotFoundException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_BLOG_CATEGORY)]
class BlogCategoryController extends AdminBaseController
{
    public function __construct(
        protected readonly BlogCategoryFacade $blogCategoryFacade,
        protected readonly BlogCategoryDataFactory $blogCategoryDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Localization $localization,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/blog/category/edit/{id}', name: 'admin_blogcategory_edit', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

        return $this->render('@ShopsysAdministration/content/blog/category/edit.html.twig', [
            'form' => $form->createView(),
            'blogCategory' => $blogCategory,
        ]);
    }

    #[Route(path: '/blog/category/new/', name: 'admin_blogcategory_new')]
    #[CanCreate]
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

        return $this->render('@ShopsysAdministration/content/blog/category/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/blog/category/list/', name: 'admin_blogcategory_list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'blog-category';
        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        if ($selectedDomainId === null) {
            $blogCategoriesWithPreloadedChildren = $this->blogCategoryFacade->getAllBlogCategoriesWithPreloadedChildren(
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );
        } else {
            $blogCategoriesWithPreloadedChildren = $this->blogCategoryFacade->getVisibleBlogCategoriesWithPreloadedChildrenOnDomain(
                $selectedDomainId,
                $this->localization->getCurrentLocaleForTranslatableEntities(),
            );
        }

        return $this->render('@ShopsysAdministration/content/blog/category/list.html.twig', [
            'blogCategoriesWithPreloadedChildren' => $blogCategoriesWithPreloadedChildren,
            'isForAllDomains' => ($selectedDomainId === null),
            'domainFilterNamespace' => $domainFilterNamespace,
            'visibilityOfBlogCategoriesIndexedById' => $selectedDomainId === null ? $this->getVisibleBlogCategoryIdsForAllDomains() : null,
        ]);
    }

    /**
     * @see node_modules/@shopsys/framework/js/admin/components/CategoryTreeSorting.js
     */
    #[Route(path: '/blog/category/apply-sorting/', methods: ['post'], condition: 'request.isXmlHttpRequest()')]
    #[CanEdit]
    public function applySortingAction(Request $request): Response
    {
        $categoriesOrderingDataJson = $request->request->get('categoriesOrderingData');
        $categoriesOrderingData = Json::decode($categoriesOrderingDataJson, true);

        $this->blogCategoryFacade->reorderByNestedSetValues($categoriesOrderingData);

        return new Response('OK - dummy');
    }

    #[Route(path: '/blog/category/delete/{id}', name: 'admin_blogcategory_delete', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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

    #[Route(path: '/blog/category/branch/{domainId}/{id}', name: 'admin_blogcategory_loadbranchjson', requirements: ['domainId' => '\d+', 'id' => '\d+'], condition: 'request.isXmlHttpRequest()')]
    #[CanView]
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

    /**
     * @return array<int, string>
     */
    protected function getVisibleBlogCategoryIdsForAllDomains(): array
    {
        $domainsCount = count($this->domain->getAll());

        return $this->blogCategoryFacade->getVisibilityOfBlogCategoriesIndexedById($domainsCount);
    }
}
