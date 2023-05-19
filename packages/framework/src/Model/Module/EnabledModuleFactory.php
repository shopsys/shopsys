<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Module;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class EnabledModuleFactory implements EnabledModuleFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\Module\EnabledModule
     */
    public function create(string $name): EnabledModule
    {
        $classData = $this->entityNameResolver->resolve(EnabledModule::class);

        return new $classData($name);
    }
}
