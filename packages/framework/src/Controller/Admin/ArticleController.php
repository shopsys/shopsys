<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\ConfirmDelete\ConfirmDeleteResponseFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanCreate;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanDelete;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanEdit;
use Shopsys\FrameworkBundle\Component\Security\Attribute\CanView;
use Shopsys\FrameworkBundle\Component\Security\Attribute\ForRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Form\Admin\Article\ArticleFormType;
use Shopsys\FrameworkBundle\Model\AdminNavigation\BreadcrumbOverrider;
use Shopsys\FrameworkBundle\Model\Article\Article;
use Shopsys\FrameworkBundle\Model\Article\ArticleDataFactory;
use Shopsys\FrameworkBundle\Model\Article\ArticleFacade;
use Shopsys\FrameworkBundle\Model\Article\Exception\ArticleNotFoundException;
use Shopsys\FrameworkBundle\Model\LegalConditions\LegalConditionsFacade;
use Shopsys\FrameworkBundle\Model\UserConsentPolicy\UserConsentPolicyFacade;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[ForRole(AdminRoleConstant::ROLE_ARTICLE)]
class ArticleController extends AdminBaseController
{
    public function __construct(
        protected readonly ArticleFacade $articleFacade,
        protected readonly ArticleDataFactory $articleDataFactory,
        protected readonly GridFactory $gridFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
        protected readonly BreadcrumbOverrider $breadcrumbOverrider,
        protected readonly ConfirmDeleteResponseFactory $confirmDeleteResponseFactory,
        protected readonly LegalConditionsFacade $legalConditionsFacade,
        protected readonly UserConsentPolicyFacade $userConsentPolicyFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/article/edit/{id}', requirements: ['id' => '\d+'])]
    #[CanEdit(methods: [HttpMethod::POST])]
    #[CanView(methods: [HttpMethod::GET])]
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

            return $this->redirectToRoute('admin_article_list');
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addErrorFlashTwig(t('Please check the correctness of all data filled.'));
        }

        $this->breadcrumbOverrider->overrideLastItem(t('Editing article - %name%', ['%name%' => $article->getName()]));

        return $this->render('@ShopsysAdministration/content/article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route(path: '/article/list/')]
    #[CanView]
    public function listAction(): Response
    {
        $gridFooter1 = $this->getGrid(Article::PLACEMENT_FOOTER_1, t('Articles in footer') . ' 1');
        $gridFooter2 = $this->getGrid(Article::PLACEMENT_FOOTER_2, t('Articles in footer') . ' 2');
        $gridFooter3 = $this->getGrid(Article::PLACEMENT_FOOTER_3, t('Articles in footer') . ' 3');
        $gridFooter4 = $this->getGrid(Article::PLACEMENT_FOOTER_4, t('Articles in footer') . ' 4');
        $gridNone = $this->getGrid(Article::PLACEMENT_NONE, t('Articles without positioning'));
        $articlesCountOnSelectedDomain = $this->articleFacade->getAllArticlesCountByDomainId($this->adminDomainTabsFacade->getSelectedDomainId());

        return $this->render('@ShopsysAdministration/content/article/list.html.twig', [
            'gridViewFooter1' => $gridFooter1->createView(),
            'gridViewFooter2' => $gridFooter2->createView(),
            'gridViewFooter3' => $gridFooter3->createView(),
            'gridViewFooter4' => $gridFooter4->createView(),
            'gridViewNone' => $gridNone->createView(),
            'articlesCountOnSelectedDomain' => $articlesCountOnSelectedDomain,
        ]);
    }

    #[Route(path: '/article/new/')]
    #[CanCreate]
    public function newAction(Request $request): Response
    {
        $selectedDomainId = $this->adminDomainTabsFacade->getSelectedDomainId();
        $articleData = $this->articleDataFactory->create($selectedDomainId);

        $form = $this->createForm(ArticleFormType::class, $articleData, [
            'article' => null,
            'domain_id' => $selectedDomainId,
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

        return $this->render('@ShopsysAdministration/content/article/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/article/delete/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
    public function deleteAction(int $id): Response
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

    #[Route(path: '/article/delete-confirm/{id}', requirements: ['id' => '\d+'])]
    #[CanDelete]
    #[CsrfProtection]
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

    #[Route(path: '/article/save-ordering/', condition: 'request.isXmlHttpRequest()')]
    #[CanEdit]
    public function saveOrderingAction(Request $request): JsonResponse
    {
        $this->articleFacade->saveOrdering($request->request->all('rowIdsByGridId'));

        $responseData = ['success' => true];

        return new JsonResponse($responseData);
    }

    protected function getGrid(string $articlePlacement, string $articlePlacementTitle): Grid
    {
        $queryBuilder = $this->articleFacade->getOrderedArticlesByDomainIdAndPlacementQueryBuilder(
            $this->adminDomainTabsFacade->getSelectedDomainId(),
            $articlePlacement,
        );

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'a.id');

        $gridId = $articlePlacement;
        $grid = $this->gridFactory->create($gridId, $dataSource, AdminRoleConstant::ROLE_ARTICLE);
        $grid->setDefaultOrder('position');
        $grid->setTitle($articlePlacementTitle);

        $grid->addColumn('name', 'a.name', t('Name'));

        $grid->addEditActionColumn('admin_article_edit', ['id' => 'a.id']);
        $grid->addDeleteActionColumn('admin_article_deleteconfirm', ['id' => 'a.id'])
            ->setAjaxConfirm();

        $grid->enableMultipleDragAndDrop();
        $grid->setTheme('@ShopsysAdministration/content/article/listGrid.html.twig');

        return $grid;
    }
}
