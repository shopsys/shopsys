<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class EnabledModuleFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(string $name): EnabledModule
    {
        $entityClassName = $this->entityNameResolver->resolve(EnabledModule::class);

        return new $entityClassName($name);
    }
}
