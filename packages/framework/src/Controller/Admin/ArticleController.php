<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AdminBaseController
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleFacade $articleFacade
     * @param \Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface $articleDataFactory
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     * @param \Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider $breadcrumbOverrider
     * @param \Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory $confirmDeleteResponseFactory
     * @param \Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade $legalConditionsFacade
     * @param \Shopsys\FrameworkBundle\Model\Cookies\CookiesFacade $cookiesFacade
     */
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly ArticleDataFactoryInterface $articleDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
        protected readonly CookiesFacade $cookiesFacade,
    ) {
    }

    /**
     * @Route("/article/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, $id): \Symfony\Component\HttpFoundation\Response
    {
        $article = $this->articleFacade->getById($id);
        $articleData = $this->articleDataFactory->createFromArticle($article);

        $form = $this->createForm(ArticleFormType::class, $articleData, [
            'article' => $article,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->articleFacade->edit($id, $articleData);

            $this
                ->addSuccessFlashTwig(
                    t('Article <strong><a href="{{ url }}">{{ name }}</a></strong> modified'),
                    [
                        'name' => $article->getName(),
                        'url' => $this->generateUrl('admin_article_edit', ['id' => $article->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_article_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing article - %name%', ['%name%' => $article->getName()]));

        return $this->render('@ShopsysFramework/Admin/Content/Article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    /**
     * @Route("/article/list/")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(): \Symfony\Component\HttpFoundation\Response
    {
        $gridFooter = $this->getGrid(Article::PLACEMENT_FOOTER);
        $gridNone = $this->getGrid(Article::PLACEMENT_NONE);
        $articlesCountOnSelectedDomain = $this->articleFacade->getAllArticlesCountByDomainId(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
        );

        return $this->render('@ShopsysFramework/Admin/Content/Article/list.html.twig', [
            'gridViewFooter' => $gridFooter->createView(),
            'gridViewNone' => $gridNone->createView(),
            'articlesCountOnSelectedDomain' => $articlesCountOnSelectedDomain,
        ]);
    }

    /**
     * @Route("/article/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $articleData = $this->articleDataFactory->create();

        $form = $this->createForm(ArticleFormType::class, $articleData, [
            'article' => null,
            'domain_id' => $this->adminDomainTabsFacade->getSelectedDomainId(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $article = $this->articleFacade->create($articleData);

            $this
                ->addSuccessFlashTwig(
                    t('Article <strong><a href="{{ url }}">{{ name }}</a></strong> created'),
                    [
                        'name' => $article->getName(),
                        'url' => $this->generateUrl('admin_article_edit', ['id' => $article->getId()]),
                    ],
                );

            return $this->redirectToRoute('admin_article_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        return $this->render('@ShopsysFramework/Admin/Content/Article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/article/delete/{id}", requirements={"id" = "\d+"})
     * @CsrfProtection
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($id): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        try {
            $fullName = $this->articleFacade->getById($id)->getName();

            $this->articleFacade->delete($id);

            $this->addSuccessFlashTwig(
                t('Article <strong>{{ name }}</strong> deleted'),
                [
                    'name' => $fullName,
                ],
            );
        } catch (ArticleNotFoundException $ex) {
            $this->addErrorFlash(t('Selected article doesn\'t exist.'));
        }

        return $this->redirectToRoute('admin_article_list');
    }

    /**
     * @Route("/article/delete-confirm/{id}", requirements={"id" = "\d+"})
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteConfirmAction($id): \Symfony\Component\HttpFoundation\Response
    {
        $article = $this->articleFacade->getById($id);

        if ($this->legalConditionsFacade->isArticleUsedAsLegalConditions($article)) {
            $message = t(
                'Article "%name%" set for displaying legal conditions. This setting will be lost. Do you really want to delete it?',
                ['%name%' => $article->getName()],
            );
        } elseif ($this->cookiesFacade->isArticleUsedAsCookiesInfo($article)) {
            $message = t(
                'Article "%name%" set for displaying cookies information. This setting will be lost. Do you really want to delete it?',
                ['%name%' => $article->getName()],
            );
        } else {
            $message = t('Do you really want to remove this article?');
        }

        return $this->confirmDeleteResponseFactory->createDeleteResponse($message, 'admin_article_delete', $id);
    }

    /**
     * @Route("/article/save-ordering/", condition="request.isXmlHttpRequest()")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function saveOrderingAction(Request $request): \Symfony\Component\HttpFoundation\JsonResponse
    {
        $this->articleFacade->saveOrdering($request->get('rowIdsByGridId'));

        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }

    /**
     * @param string $articlePlacement
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid($articlePlacement): \Shopsys\FrameworkBundle\Component\Grid\Grid
    {
        $queryBuilder = $this->articleFacade->getOrderedArticlesByDomainIdAndPlacementQueryBuilder(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $articlePlacement,
        );

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'a.id');

        $gridId = $articlePlacement;
        $grid = $this->gridFactory->create($gridId, $dataSource);
        $grid->setDefaultOrder('position');

        $grid->addColumn('name', 'a.name', t('Name'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_article_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_article_deleteconfirm', ['id' => 'a.id'])
            ->setAjaxConfirm();

        $grid->enableMultipleDragAndDrop();
        $grid->setTheme('@ShopsysFramework/Admin/Content/Article/listGrid.html.twig');

        return $grid;
    }
}
