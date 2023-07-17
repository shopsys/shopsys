<?php

declare(strict_types=1);

namespace App\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;

class BlogArticleGridFactory
{
    /**
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        private BlogArticleRepository $blogArticleRepository,
        private GridFactory $gridFactory,
        private Domain $domain,
    ) {
    }

    /**
     * @param int $domainId
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId): Grid
    {
        if ($domainId === 0) {
            $locale = $this->domain->getLocale();
            $queryBuilder = $this->blogArticleRepository->getAllBlogArticlesByLocaleQueryBuilder(
                $locale,
            );
        } else {
            $locale = $this->domain->getDomainConfigById($domainId)->getLocale();
            $queryBuilder = $this->blogArticleRepository->getBlogArticlesByDomainIdAndLocaleQueryBuilderIfInBlogCategory(//getBlogArticlesByDomainIdAndLocaleQueryBuilder(
                $domainId,
                $locale,
            );
        }

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blog_article', $dataSource);
        $grid->setDefaultOrder('createdAt DESC');

        $grid->addColumn('name', 'bat.name', t('Name'));
        $grid->addColumn('createdAt', 'ba.createdAt', t('Date of creation'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);
        $grid->addDeleteActionColumn('admin_blogarticle_deleteconfirm', ['id' => 'ba.id'])
            ->setAjaxConfirm();

        $grid->setTheme('Admin/Content/Blog/Article/listGrid.html.twig');

        return $grid;
    }
}
