<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User\Role;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CustomerUserRoleGroupFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(CustomerUserRoleGroupData $data): CustomerUserRoleGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(CustomerUserRoleGroup::class);

        return new $entityClassName($data);
    }
}
