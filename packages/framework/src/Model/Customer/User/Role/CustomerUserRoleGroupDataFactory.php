<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class CustomerUserRoleGroupDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
    ) {
    }

    public function create(): CustomerUserRoleGroupData
    {
        return $this->createInstance();
    }

    protected function createInstance(): CustomerUserRoleGroupData
    {
        return new CustomerUserRoleGroupData();
    }

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
