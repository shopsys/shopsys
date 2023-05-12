<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorGridLimitFactory implements AdministratorGridLimitFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $gridId
     * @param int $limit
     * @return \Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridLimit
     */
    public function create(Administrator $administrator, string $gridId, int $limit): AdministratorGridLimit
    {
        $classData = $this->entityNameResolver->resolve(AdministratorGridLimit::class);

        return new $classData($administrator, $gridId, $limit);
    }
}
