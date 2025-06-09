<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Security\Roles;

class BlogArticleGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly BlogArticleRepository $blogArticleRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(QueryBuilder $queryBuilder): Grid
    {
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blog_article', $dataSource, Roles::ROLE_BLOG_ARTICLE_FULL);
        $grid->setDefaultOrder('createdAt DESC');
        $grid->enablePaging();

        $grid->addColumn('name', 'bat.name', t('Name'));
        $grid->addColumn('createdAt', 'ba.createdAt', t('Date of creation'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);
        $grid->addDeleteActionColumn('admin_blogarticle_deleteconfirm', ['id' => 'ba.id'])
            ?->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Blog/Article/listGrid.html.twig');

        return $grid;
    }
}
