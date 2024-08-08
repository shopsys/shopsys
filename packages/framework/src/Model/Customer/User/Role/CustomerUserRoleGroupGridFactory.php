<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridFactoryInterface;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource;
use Shopsys\FrameworkBundle\Model\Localization\Localization;

class CustomerUserRoleGroupGridFactory implements GridFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository $customerUserRoleGroupRepository
     * @param \Shopsys\FrameworkBundle\Component\Grid\GridFactory $gridFactory
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
        protected readonly GridFactory $gridFactory,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Component\Grid\Grid
     */
    public function create(): Grid
    {
        $queryBuilder = $this->getGridQueryBuilder();

        $dataSource = new QueryBuilderDataSource($queryBuilder, 'cug.id');

        $grid = $this->gridFactory->create('customerUserRoleGroupsList', $dataSource);
        $grid->setDefaultOrder('name');

        $grid->addColumn('name', 'cugt.name', t('Role name'), true);

        $grid->setActionColumnClassAttribute('table-col table-col-10');
        $grid->addEditActionColumn('admin_superadmin_customer_user_role_group_edit', ['id' => 'cug.id']);

        return $grid;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getGridQueryBuilder(): QueryBuilder
    {
        return $this->customerUserRoleGroupRepository->getAllQueryBuilderByLocale(
            $this->localization->getAdminLocale(),
        );
    }
}
