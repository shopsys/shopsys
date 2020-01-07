<?php

declare(strict_types = 1);

namespace App\Model\Blog\Article;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class BlogArticleGridFactory
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\GridFactory
     */
    private $gridFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \App\Model\Blog\Article\BlogArticleRepository
     */
    private $blogArticleRepository;

    /**
     * @param \App\Model\Blog\Article\BlogArticleRepository $blogArticleRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        BlogArticleRepository $blogArticleRepository,
        GridFactory $gridFactory,
        Localization $localization
    ) {
        $this->gridFactory = $gridFactory;
        $this->localization = $localization;
        $this->blogArticleRepository = $blogArticleRepository;
    }

    /**
     * @param int $domainId
     * @throws \Shopsys\FrameworkBundle\Component\Grid\Exception\DuplicateColumnIdException
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId): Grid
    {
        if ($domainId === 0) {
            $queryBuilder = $this->blogArticleRepository->getAllBlogArticlesByLocaleQueryBuilder(
                $this->localization->getAdminLocale()
            );
        } else {
            $queryBuilder = $this->blogArticleRepository->getBlogArticlesByDomainIdAndLocaleQueryBuilder(
                $domainId,
                $this->localization->getAdminLocale()
            );
        }

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blog_article', $dataSource);
        $grid->setDefaultOrder('createdAt DESC');

        $grid->addColumn('name', 'bat.name', t('Name'));
        $grid->addColumn('createdAt', 'ba.createdAt', t('Created at'));

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);
        $grid->addDeleteActionColumn('admin_blogarticle_deleteconfirm', ['id' => 'ba.id'])
            ->setAjaxConfirm();

        $grid->setTheme('Admin/Content/Blog/Article/listGrid.html.twig');

        return $grid;
    }
}
