<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Blog\BlogArticleFormType;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade;
use Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleGridFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BlogArticleController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleFacade $blogArticleFacade
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleDataFactory $blogArticleDataFactory
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleGridFactory $blogArticleGridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade
     */
    public function __construct(
        protected readonly BlogArticleFacade $blogArticleFacade,
        protected readonly BlogArticleDataFactory $blogArticleDataFactory,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly BlogArticleGridFactory $blogArticleGridFactory,
        protected readonly AdminDomainFilterTabsFacade $adminDomainFilterTabsFacade,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/blog/article/list/', name: 'admin_blogarticle_list')]
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
        );

        $grid = $this->blogArticleGridFactory->create($queryBuilder);

        return $this->render('@ShopsysFramework/Admin/Content/Blog/Article/list.html.twig', [
            'quickSearchForm' => $quickSearchForm->createView(),
            'gridView' => $grid->createView(),
            'domainFilterNamespace' => $domainFilterNamespace,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/blog/article/edit/{id}', name: 'admin_blogarticle_edit', requirements: ['id' => '\d+'])]
    public function editAction(Request $request, int $id): Response
    {
        $blogArticle = $this->blogArticleFacade->getById($id);
        $blogArticleData = $this->blogArticleDataFactory->createFromBlogArticle($blogArticle);

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => $blogArticle,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing blog article - %name%', ['%name%' => $blogArticle->getName()]));

        return $this->render('@ShopsysFramework/Admin/Content/Blog/Article/edit.html.twig', [
            'form' => $form->createView(),
            'blogArticle' => $blogArticle,
        ]);
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/blog/article/new/', name: 'admin_blogarticle_new')]
    public function newAction(Request $request): Response
    {
        $blogArticleData = $this->blogArticleDataFactory->create();

        $form = $this->createForm(BlogArticleFormType::class, $blogArticleData, [
            'blogArticle' => null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Blog/Article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/blog/article/delete/{id}', name: 'admin_blogarticle_delete', requirements: ['id' => '\d+'])]
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

    /**
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    #[Route(path: '/blog/article/delete-confirm/{id}', name: 'admin_blogarticle_deleteconfirm', requirements: ['id' => '\d+'])]
    public function deleteConfirmAction(int $id): Response
    {
        $message = t('Do you really want to remove this blog article?');

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_blogarticle_delete', $id);
    }
}
