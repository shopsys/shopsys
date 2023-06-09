<?php

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorFactory implements AdministratorFactoryInterface
{
    protected EntityNameResolver $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $data
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function create(AdministratorData $data): Administrator
    {
        $classData = $this->entityNameResolver->resolve(Administrator::class);

        return new $classData($data);
    }
}
