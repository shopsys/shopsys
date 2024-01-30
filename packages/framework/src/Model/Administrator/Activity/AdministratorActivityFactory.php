<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFactory implements AdministratorActivityFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\Administrator $administrator
     * @param string $ipAddress
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Activity\AdministratorActivity
     */
    public function create(Administrator $administrator, string $ipAddress): AdministratorActivity
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorActivity::class);

        return new $entityClassName($administrator, $ipAddress);
    }
}
