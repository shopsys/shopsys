<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Controller\Admin;

use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Blog\Author\BlogArticleAuthorRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BlogArticleAuthorPickerController extends AdminBaseController
{
    public function __construct(
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly BlogArticleAuthorRepository $blogArticleAuthorRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Route(path: '/blog-article-author-picker/pick-single/{jsInstanceId}/', defaults: ['jsInstanceId' => '__js_instance_id__'])]
    #[RequireRole(SystemRole::ADMIN)]
    public function pickSingleAction(Request $request, string $jsInstanceId): Response
    {
        $quickSearchData = new QuickSearchFormData();
        $quickSearchForm = $this->createForm(QuickSearchFormType::class, $quickSearchData);
        $quickSearchForm->handleRequest($request);

        $queryBuilder = $this->blogArticleAuthorRepository->getAllBlogArticleAuthorsQueryBuilder($quickSearchData->text);
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blogArticleAuthorPicker', $dataSource, AdminRoleConstant::ROLE_BLOG_ARTICLE_AUTHOR);
        $grid->enablePaging();
        $grid->setDefaultOrder('name');
        $this->administratorGridFacade->restoreAndRememberGridLimit($this->getCurrentAdministrator(), $grid);

        $grid->addColumn('name', 'ba.name', t('Name'), true);
        $grid->addColumn('select', 'ba.id', '')
            ->setClassAttribute('text-center text-nowrap');

        $grid->setTheme('@ShopsysAdministration/content/blogArticleAuthorPicker/listGrid.html.twig', [
            'jsInstanceId' => $jsInstanceId,
        ]);

        return $this->render('@ShopsysAdministration/content/blogArticleAuthorPicker/list.html.twig', [
            'gridView' => $grid->createView(),
            'quickSearchForm' => $quickSearchForm->createView(),
        ]);
    }
}
