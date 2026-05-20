<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Blog\Article;

use Doctrine\ORM\Query\Expr\Join;
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

    public function create(QueryBuilder $queryBuilder, ?int $selectedDomainId = null): Grid
    {
        if ($selectedDomainId !== null) {
            $queryBuilder
                ->addSelect('bad.status AS domainStatus, bad.publishDate AS domainPublishDate, bad.visible AS domainVisible')
                ->join('ba.domains', 'bad', Join::WITH, 'bad.domainId = :gridDomainId')
                ->setParameter('gridDomainId', $selectedDomainId);
        } else {
            $queryBuilder
                ->addSelect('MIN(bad.status) AS domainStatus')
                ->addSelect('CASE WHEN MIN(bad.status) = MAX(bad.status) THEN false ELSE true END AS mixedStatus')
                ->addSelect('MAX(bad.publishDate) AS domainPublishDate')
                ->addSelect('MIN(CASE WHEN bad.visible = true THEN 1 ELSE 0 END) AS domainVisible')
                ->join('ba.domains', 'bad')
                ->groupBy('ba.id, bat.name, bat.description, bat.perex, bat.id, ba.createdAt');
        }

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'ba.id');

        $grid = $this->gridFactory->create('blog_article', $dataSource, AdminRoleConstant::ROLE_BLOG_ARTICLE);
        $grid->setDefaultOrder('createdAt DESC');
        $grid->enablePaging();

        $grid->addColumn('name', 'bat.name', t('Name'));
        $grid->addColumn('status', 'domainStatus', t('Status'));
        $grid->addColumn('publishDate', 'domainPublishDate', t('Date of publication'));
        $grid->addColumn('createdAt', 'ba.createdAt', t('Date of creation'));

        $grid->addEditActionColumn('admin_blogarticle_edit', ['id' => 'ba.id']);
        $grid->addDeleteActionColumn('admin_blogarticle_deleteconfirm', ['id' => 'ba.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/blog/article/listGrid.html.twig');

        return $grid;
    }
}
