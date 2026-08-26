<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
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
use Shopsys\FrameworkBundle\Form\Admin\Blog\BlogArticleFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleGridFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\Exception\BlogArticleStatusTransitionException;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_BLOG_ARTICLE)]
class BlogArticleController extends AdminBaseController
{
    public function __construct(
        protected readonly BlogArticleFacade $blogArticleFacade,
        protected readonly BlogArticleDataFactory $blogArticleDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly BlogArticleGridFactory $blogArticleGridFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
        protected readonly Localization $localization,
        protected readonly Domain $domain,
    ) {
    }

    #[Route(path: '/blog/article/list/', name: 'admin_blogarticle_list')]
    #[CanView]
    public function listAction(Request $request): Response
    {
        $domainFilterNamespace = 'blog-article';
        $selectedDomainId = $this->adminDomainFilterTabsFacade->getSelectedDomainId($domainFilterNamespace);

        $quickSearchData = new QuickSearchFormData();
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->blogArticleFacade->getQueryBuilderForQuickSearch(
            $selectedDomainId,
            $quickSearchForm->getData(),
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );

        $grid = $this->blogArticleGridFactory->create($queryBuilder, $selectedDomainId);

        return $this->render('@ShopsysAdministration/content/blog/article/list.html.twig', [
            'quickSearchForm' => $quickSearchForm->createView(),
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
        ]);
    }

    #[Route(path: '/blog/article/edit/{id}', name: 'admin_blogarticle_edit', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
    public function editAction(Request $request, int $id): Response
    {
        $blogArticle = $this->blogArticleFacade->getById($id);
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => $blogArticle,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->blogArticleFacade->edit($id, $blogArticleData);

                $this
                    ->addSuccessFlashTwig(
                        t('Blog article <strong><a href="{{ url }}">{{ name }}</a></strong> has been updated'),
                        [
                            'name' => $blogArticle->getName(),
                            'url' => $this->generateUrl('admin_blogarticle_edit', ['id' => $blogArticle->getId()]),
                        ],
                    );

                return $this->redirectToRoute('admin_blogarticle_list');
            } catch (BlogArticleStatusTransitionException $e) {
                $this->addErrorFlash($e->getMessage());
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing blog article - %name%', ['%name%' => $blogArticle->getName()]));

        return $this->render('@ShopsysAdministration/content/blog/article/edit.html.twig', [
            'form' => $form->createView(),
            'blogArticle' => $blogArticle,
            'domains' => $this->domain->getAdminEnabledDomains(),
        ]);
    }

    #[Route(path: '/blog/article/new/', name: 'admin_blogarticle_new')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $blogArticle = $this->blogArticleFacade->create($blogArticleData);

                $this
                    ->addSuccessFlashTwig(
                        t('Blog article <strong><a href="{{ url }}">{{ name }}</a></strong> has been created'),
                        [
                            'name' => $blogArticle->getName(),
                            'url' => $this->generateUrl('admin_blogarticle_edit', ['id' => $blogArticle->getId()]),
                        ],
                    );

                return $this->redirectToRoute('admin_blogarticle_list');
            } catch (BlogArticleStatusTransitionException $e) {
                $this->addErrorFlash($e->getMessage());
            }
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysAdministration/content/blog/article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/blog/article/delete/{id}', name: 'admin_blogarticle_delete', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->blogArticleFacade->getById($id)->getName();

            $this->blogArticleFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Blog article <strong>{{ name }}</strong> has been removed'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (ArticleNotFoundException $ex) {
            $this->addErrorFlash(t('Selected blog article does not exist.'));
        }

        return $this->redirectToRoute('admin_blogarticle_list');
    }

    #[Route(path: '/blog/article/delete-confirm/{id}', name: 'admin_blogarticle_deleteconfirm', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this blog article?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_blogarticle_delete', $id);
    }
}
