<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Component\Security\Role\AdminRoleConstant;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;

class InquiryGridFactory
{
    public function __construct(
        protected readonly GridFactory $gridFactory,
        protected readonly AdministratorGridFacade $administratorGridFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    public function createView(
        QueryBuilder $queryBuilder,
        Administrator $administrator,
    ): GridView {
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'i.id');

        $grid = $this->gridFactory->create('inquiryList', $dataSource, AdminRoleConstant::ROLE_INQUIRY);

        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('createdAt', 'i.createdAt', t('Created'), true);
        $grid->addColumn('productName', 'productName', t('Product name'), true);
        $grid->addColumn('fullName', 'fullName', t('Full name'), true);
        $grid->addColumn('company', 'company', t('Company (Company number)'), true);
        $grid->addColumn('email', 'i.email', t('Email'), true);
        $grid->addColumn('telephone', 'telephone', t('Phone'));

        $grid->addActionColumn('file-all', t('Show detail'), 'admin_inquiry_detail', ['id' => 'i.id']);

        $grid->setTheme('@ShopsysAdministration/content/inquiry/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
