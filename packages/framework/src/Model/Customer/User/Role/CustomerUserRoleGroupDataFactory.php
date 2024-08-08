<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CustomerUserRoleGroupDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup $administratorRoleGroup
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData
     */
    public function createFromCustomerUserRoleGroup(
        CustomerUserRoleGroup $administratorRoleGroup,
    ): CustomerUserRoleGroupData {
        $customerUserRoleGroupData = $this->createInstance();

        foreach ($this->domain->getAllLocales() as $locale) {
            $customerUserRoleGroupData->names[$locale] = $administratorRoleGroup->getName($locale);
        }
        $customerUserRoleGroupData->uuid = $administratorRoleGroup->getUuid();
        $customerUserRoleGroupData->roles = $administratorRoleGroup->getRoles();

        return $customerUserRoleGroupData;
    }
}
