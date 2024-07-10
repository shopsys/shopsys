<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRoleGroupFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupDataRepository $customerUserRoleGroupDataRepository
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupSetting $customerUserRoleGroupSetting
     */
    public function __construct(
        protected readonly CustomerUserRoleGroupDataRepository $customerUserRoleGroupDataRepository,
        protected readonly CustomerUserRoleGroupSetting $customerUserRoleGroupSetting,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup[]
     */
    public function getAll(): array
    {
        return $this->customerUserRoleGroupDataRepository->getAll();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getDefaultCustomerUserRoleGroup(): CustomerUserRoleGroup
    {
        return $this->customerUserRoleGroupSetting->getDefaultCustomerUserRoleGroup();
    }
}
