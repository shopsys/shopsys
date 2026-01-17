<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator\Activity;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Administrator\Administrator;

class AdministratorActivityFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(Administrator $administrator, string $ipAddress): AdministratorActivity
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorActivity::class);

        return new $entityClassName($administrator, $ipAddress);
    }
}
