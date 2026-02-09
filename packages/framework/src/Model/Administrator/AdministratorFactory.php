<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(AdministratorData $data): Administrator
    {
        $entityClassName = $this->entityNameResolver->resolve(Administrator::class);

        return new $entityClassName($data);
    }
}
