<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;

class BlogArticleGridFactory
{
    public function __construct(
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Domain $domain,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function create(QueryBuilder $queryBuilder): Grid
    {
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blog_article', $dataSource, AdminRoleConstant::ROLE_BLOG_ARTICLE);
        $grid->setDefaultOrder('createdAt DESC');
        $grid->enablePaging();

        $grid->addColumn('name', 'bat.name', t('Name'));
        $grid->addColumn('createdAt', 'ba.createdAt', t('Date of creation'));

        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);
        $grid->addDeleteActionColumn('admin_blogarticle_deleteconfirm', ['id' => 'ba.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/blog/article/listGrid.html.twig');

        return $grid;
    }
}
