<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Seo\Page;

use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;

class SeoPageGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Seo\Page\SeoPageRepository $seoPageRepository
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly SeoPageRepository $seoPageRepository,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(int $domainId): Grid
    {
        $queryBuilder = $this->seoPageRepository->getAllQueryBuilder()
            ->andWhere('spd.domainId = :domainId')
            ->setParameter('domainId', $domainId);

        $dataSource = new QueryBuilderDataSource(
            $queryBuilder,
            'sp.id',
        );

        $grid = $this->gridFactory->create('seo_page', $dataSource, 'ROLE_SEO');
        $grid->enablePaging();

        $grid->addColumn('pageName', 'sp.pageName', t('Page name'), true);
        $grid->addColumn('pageSlug', 'spd.pageSlug', t('Page slug'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_seopage_edit', ['id' => 'sp.id']);
        $grid->addDeleteActionColumn('admin_seopage_deleteconfirm', ['id' => 'sp.id'])
            ->setAjaxConfirm();

        $grid->setTheme('@ShopsysFramework/Admin/Content/Seo/Page/listGrid.html.twig');

        return $grid;
    }
}
