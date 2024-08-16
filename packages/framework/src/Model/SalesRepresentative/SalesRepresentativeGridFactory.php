<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class SalesRepresentativeGridFactory implements GridFactoryInterface
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeFacade $salesRepresentativeFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->salesRepresentativeFacade->getAllQueryBuilder();
        $dataSource = new QueryBuilderDataSource($queryBuilder, 'sr.id');

        $grid = $this->gridFactory->create('salesRepresentativesList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('firstName', 'sr.firstName', t('First name'), true);
        $grid->addColumn('lastName', 'sr.lastName', t('Last name'), true);
        $grid->addColumn('telephone', 'sr.telephone', t('Telephone'), true);
        $grid->addColumn('email', 'sr.email', t('E-mail'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_salesrepresentative_edit', ['id' => 'sr.id']);
        $grid->addDeleteActionColumn('admin_salesrepresentative_deleteconfirm', ['id' => 'sr.id'])
            ->setAjaxConfirm();

        return $grid;
    }
}
