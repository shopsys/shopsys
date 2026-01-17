<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSourceFactory;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class CustomerUserRoleGroupGridFactory implements GridFactoryInterface
{
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
        protected readonly QueryBuilderDataSourceFactory $queryBuilderDataSourceFactory,
    ) {
    }

    #[Override]
    public function create(?string $roleConstant): Grid
    {
        $queryBuilder = $this->getGridQueryBuilder();

        $dataSource = $this->queryBuilderDataSourceFactory->create($queryBuilder, 'cug.id');

        $grid = $this->gridFactory->create('customerUserRoleGroupsList', $dataSource, $roleConstant);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'cugt.name', t('Role group name'), true);

        $grid->addEditActionColumn('admin_superadmin_customer_user_role_group_edit', ['id' => 'cug.id']);
        $grid->addDeleteActionColumn('admin_superadmin_customer_user_role_group_delete', ['id' => 'cug.id'])
            ->setConfirmMessage(t('Do you really want to remove this customer user role group?'));

        return $grid;
    }

    protected function getGridQueryBuilder(): QueryBuilder
    {
        return $this->customerUserRoleGroupRepository->getAllQueryBuilderByLocale(
            $this->localization->getCurrentLocaleForTranslatableEntities(),
        );
    }
}
