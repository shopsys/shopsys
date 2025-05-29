<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Watchdog;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Shopsys\FrameworkBundle\Model\Security\Roles;

class WatchdogGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
    ) {
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function createView(
        QueryBuilder $queryBuilder,
        Administrator $administrator,
    ): GridView {
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'productId');

        $grid = $this->gridFactory->create('watchdogList', $dataSource, Roles::ROLE_WATCHDOG_FULL);

        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('productCatnum', 'productCatnum', t('Product catnum'), true);
        $grid->addColumn('productName', 'productName', t('Product name'), true);
        $grid->addColumn('watchdogCount', 'watchdogCount', t('Number of watchdogs'), true);
        $grid->addActionColumn('file-all', t('Show detail'), 'admin_watchdog_detail', ['id' => 'productId']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Watchdog/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @return \Shopsys\FrameworkBundle\Component\Grid\GridView
     */
    public function createDetailView(
        QueryBuilder $queryBuilder,
        Administrator $administrator,
    ): GridView {
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'w.id');

        $grid = $this->gridFactory->create('watchdogList', $dataSource, Roles::ROLE_WATCHDOG_FULL);

        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('watchdogEmail', 'w.email', t('Email'), true);
        $grid->addColumn('createdAt', 'w.createdAt', t('Created at'), true);
        $grid->addColumn('updatedAt', 'w.updatedAt', t('Updated at'), true);
        $grid->addColumn('validUntil', 'w.validUntil', t('Valid until'), true);
        $grid->addDeleteActionColumn('admin_watchdog_delete', ['id' => 'w.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Watchdog/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
