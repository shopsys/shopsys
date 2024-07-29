<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\Setting\Setting;

class CustomerUserRoleGroupSetting
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Setting\Setting $setting
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupRepository $customerUserRoleGroupRepository
     */
    public function __construct(
        protected readonly Setting $setting,
        protected readonly CustomerUserRoleGroupRepository $customerUserRoleGroupRepository,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function getDefaultCustomerUserRoleGroup(): CustomerUserRoleGroup
    {
        $defaultCustomerUserRoleGroupId = $this->setting->get(Setting::CUSTOMER_USER_DEFAULT_GROUP_ROLE_ID);

        return $this->customerUserRoleGroupRepository->getById($defaultCustomerUserRoleGroupId);
    }
}
