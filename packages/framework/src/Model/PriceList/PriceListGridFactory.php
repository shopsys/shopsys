<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\PriceList;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;

class PriceListGridFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade $administratorGridFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly Domain $domain,
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
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'pl.id');

        $grid = $this->gridFactory->create('priceList', $dataSource);

        $grid->enablePaging();
        $grid->setDefaultOrder('lastUpdate', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('name', 'pl.name', t('Price list name'), true);

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'pl.domainId', t('Domain'), true);
        }

        $grid->addColumn('lastUpdate', 'pl.lastUpdate', t('Last update'), true);
        $grid->addColumn('validFrom', 'pl.validFrom', t('Valid from'), true);
        $grid->addColumn('validTo', 'pl.validTo', t('Valid to'), true);
        $grid->addColumn('validityStatus', 'validityStatus', t('Status'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_pricelist_edit', ['id' => 'pl.id']);
        $grid->addDeleteActionColumn('admin_pricelist_delete', ['id' => 'pl.id'])
            ->setConfirmMessage(
                t('Do you really want to remove this product list? Special prices for products in this list will be removed.'),
            );

        $grid->setTheme('@ShopsysFramework/Admin/Content/PriceList/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
