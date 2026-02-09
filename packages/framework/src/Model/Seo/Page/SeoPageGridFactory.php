<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;

class SeoPageGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly SeoPageRepository $seoPageRepository,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function create(int $domainId): Grid
    {
        $queryBuilder = $this->seoPageRepository->getAllQueryBuilder()
            ->andWhere('spd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        $dataSource = $this->queryBuilderDataSourceFactory->create(
            $queryBuilder,
            'sp.id',
        );

        $grid = $this->gridFactory->create('seo_page', $dataSource, AdminRoleConstant::ROLE_SEO_PAGES);
        $grid->enablePaging();

        $grid->addColumn('pageName', 'sp.pageName', t('Page name'), true);
        $grid->addColumn('pageSlug', 'spd.pageSlug', t('Page slug'), true);

        $grid->addEditActionColumn('admin_seopage_edit', ['id' => 'sp.id']);
        $grid->addDeleteActionColumn('admin_seopage_deleteconfirm', ['id' => 'sp.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysAdministration/content/seo/page/listGrid.html.twig');

        return $grid;
    }
}
