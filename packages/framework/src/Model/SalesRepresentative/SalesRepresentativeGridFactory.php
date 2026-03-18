<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class SalesRepresentativeGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly SalesRepresentativeFacade $salesRepresentativeFacade,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->salesRepresentativeFacade->getAllQueryBuilder();
        $queryBuilder->addSelect('CONCAT(COALESCE(sr.telephonePrefix, \'\'), sr.telephoneNumber) as telephone');
        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'sr.id');

        $grid = $this->gridFactory->create('salesRepresentativesList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('lastName', 'sr.lastName', t('Last name'), true);
        $grid->addColumn('firstName', 'sr.firstName', t('First name'), true);
        $grid->addColumn('email', 'sr.email', t('E-mail'), true);
        $grid->addColumn('telephone', 'telephone', t('Telephone'));

        $grid->addEditActionColumn('admin_salesrepresentative_edit', ['id' => 'sr.id']);
        $grid->addDeleteActionColumn('admin_salesrepresentative_deleteconfirm', ['id' => 'sr.id'])
            ->setAjaxConfirm();

        return $grid;
    }
}
