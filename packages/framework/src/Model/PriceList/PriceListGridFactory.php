<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;

class PriceListGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly Domain $domain,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function createView(
        QueryBuilder $queryBuilder,
        Administrator $administrator,
    ): GridView {
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'pl.id');

        $grid = $this->gridFactory->create('priceList', $dataSource, AdminRoleConstant::ROLE_PRICE_LIST);

        $grid->enablePaging();
        $grid->setDefaultOrder('lastUpdate', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('name', 'pl.name', t('Price list name'), true);
        $grid->addColumn('validFrom', 'pl.validFrom', t('Valid from'), true);
        $grid->addColumn('validTo', 'pl.validTo', t('Valid to'), true);

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'pl.domainId', t('Domain'), true)->setClassAttribute('w-1 d-none d-md-table-cell text-center');
        }

        $grid->addColumn('validityStatus', 'validityStatus', t('Status'), true);
        $grid->addColumn('lastUpdate', 'pl.lastUpdate', t('Last update'), true);

        $grid->addEditActionColumn('admin_pricelist_edit', ['id' => 'pl.id']);
        $grid->addDeleteActionColumn('admin_pricelist_delete', ['id' => 'pl.id'])
            ->setConfirmMessage(
                t('Do you really want to remove this product list? Special prices for products in this list will be removed.'),
            );
        $grid->addActionColumn('download', 'Export CSV', 'admin_pricelist_export', ['id' => 'pl.id']);

        $grid->setActionColumnClassAttribute('text-center');
        $grid->setTheme('@ShopsysAdministration/content/priceList/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
