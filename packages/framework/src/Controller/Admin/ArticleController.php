<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @param \Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade $userConsentPolicyFacade
     * @param \Shopsys\FrameworkBundle\Component\Redis\CleanStorefrontCacheFacade $cleanStorefrontCacheFacade
     */
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly ArticleDataFactoryInterface $articleDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
        protected readonly UserConsentPolicyFacade $userConsentPolicyFacade,
        protected readonly CleanStorefrontCacheFacade $cleanStorefrontCacheFacade,
    ) {
    }

    /**
     * @Route("/article/edit/{id}", requirements={"id" = "\d+"})
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction(Request $request, int $id): Response
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

            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

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
    public function listAction(): Response
    {
        $gridFooter1 = $this->getGrid(Article::PLACEMENT_FOOTER_1);
        $gridFooter2 = $this->getGrid(Article::PLACEMENT_FOOTER_2);
        $gridFooter3 = $this->getGrid(Article::PLACEMENT_FOOTER_3);
        $gridFooter4 = $this->getGrid(Article::PLACEMENT_FOOTER_4);
        $gridNone = $this->getGrid(Article::PLACEMENT_NONE);
        $articlesCountOnSelectedDomain = $this->articleFacade->getAllArticlesCountByDomainId($this->adminDomainTabsFacade->getSelectedDomainId());

        return $this->render('@ShopsysFramework/Admin/Content/Article/list.html.twig', [
            'gridViewFooter1' => $gridFooter1->createView(),
            'gridViewFooter2' => $gridFooter2->createView(),
            'gridViewFooter3' => $gridFooter3->createView(),
            'gridViewFooter4' => $gridFooter4->createView(),
            'gridViewNone' => $gridNone->createView(),
            'articlesCountOnSelectedDomain' => $articlesCountOnSelectedDomain,
        ]);
    }

    /**
     * @Route("/article/new/")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request): Response
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

            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

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
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteAction(int $id): Response
    {
        try {
            $fullName = $this->articleFacade->getById($id)->getName();

            $this->articleFacade->delete($id);

            $this->cleanStorefrontCacheFacade->cleanStorefrontGraphqlQueryCache(CleanStorefrontCacheFacade::ARTICLES_QUERY_KEY_PART);

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
    public function deleteConfirmAction(int $id): Response
    {
        $article = $this->articleFacade->getById($id);

        if ($this->legalConditionsFacade->isArticleUsedAsLegalConditions($article)) {
            $message = t(
                'Article "%name%" set for displaying legal conditions. This setting will be lost. Do you really want to delete it?',
                ['%name%' => $article->getName()],
            );
        } elseif ($this->userConsentPolicyFacade->isArticleUsedAsUserConsentPolicyArticle($article)) {
            $message = t(
                'Article "%name%" set for displaying user consent policy information. This setting will be lost. Do you really want to delete it?',
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
    public function saveOrderingAction(Request $request): JsonResponse
    {
        $this->articleFacade->saveOrdering($request->get('rowIdsByGridId'));

        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }

    /**
     * @param string $articlePlacement
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    protected function getGrid($articlePlacement)
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
