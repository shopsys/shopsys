<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserRoleGroupFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupData $data
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroup
     */
    public function create(CustomerUserRoleGroupData $data): CustomerUserRoleGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(CustomerUserRoleGroup::class);

        return new $entityClassName($data);
    }
}
