<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Complaint;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;

class ComplaintGridFactory
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
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'cmp.id');

        $grid = $this->gridFactory->create('complaintList', $dataSource, AdminRoleConstant::ROLE_COMPLAINT);

        $grid->enablePaging();
        $grid->setDefaultOrder('created_at', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('number', 'cmp.number', t('Complaint Nr.'), true);
        $grid->addColumn('created_at', 'cmp.createdAt', t('Created'), true);
        $grid->addColumn('customer_name', 'customerName', t('Customer'), true);
        $grid->addColumn('order_number', 'orderNumber', t('Order Nr.'), true);
        $grid->addColumn('status_name', 'statusName', t('Status'), true);

        if ($this->domain->isMultidomain()) {
            $grid->addColumn('domain_id', 'cmp.domainId', t('Domain'), true)->setClassAttribute('w-1 d-none d-md-table-cell text-center');
        }

        $grid->addEditActionColumn('admin_complaint_edit', ['id' => 'cmp.id']);

        $grid->setActionColumnClassAttribute('text-center');
        $grid->setTheme('@ShopsysAdministration/content/complaint/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
