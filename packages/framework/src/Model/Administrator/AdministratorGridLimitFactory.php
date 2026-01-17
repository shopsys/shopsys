<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorGridLimitFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(Administrator $administrator, string $gridId, int $limit): AdministratorGridLimit
    {
        $entityClassName = $this->entityNameResolver->resolve(AdministratorGridLimit::class);

        return new $entityClassName($administrator, $gridId, $limit);
    }
}
