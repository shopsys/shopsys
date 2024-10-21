<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Inquiry;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;

class InquiryGridFactory
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
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'i.id');

        $grid = $this->gridFactory->create('inquiryList', $dataSource);

        $grid->enablePaging();
        $grid->setDefaultOrder('createdAt', DataSourceInterface::ORDER_DESC);

        $grid->addColumn('productName', 'productName', t('Product name'), true);
        $grid->addColumn('fullName', 'fullName', t('Full name'), true);
        $grid->addColumn('email', 'i.email', t('Email'), true);
        $grid->addColumn('telephone', 'i.telephone', t('Phone'));
        $grid->addColumn('company', 'company', t('Company (Company number)'), true);
        $grid->addColumn('createdAt', 'i.createdAt', t('Created'), true);

        $grid->addActionColumn('file-all', t('Show detail'), 'admin_inquiry_detail', ['id' => 'i.id']);

        $grid->setTheme('@ShopsysFramework/Admin/Content/Inquiry/listGrid.html.twig');

        $this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

        return $grid->createView();
    }
}
