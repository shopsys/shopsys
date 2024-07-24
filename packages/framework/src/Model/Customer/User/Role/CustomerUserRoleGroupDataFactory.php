<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

class CustomerUserRoleGroupDataFactory
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData
     */
    public function create(): CustomerUserRoleGroupData
    {
        return $this->createInstance();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData
     */
    protected function createInstance(): CustomerUserRoleGroupData
    {
        return new CustomerUserRoleGroupData();
    }
}
