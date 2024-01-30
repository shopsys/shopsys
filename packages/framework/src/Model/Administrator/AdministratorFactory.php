<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Administrator;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AdministratorFactory implements AdministratorFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Administrator\AdministratorData $data
     * @return \Shopsys\FrameworkBundle\Model\Administrator\Administrator
     */
    public function create(AdministratorData $data): Administrator
    {
        $entityClassName = $this->entityNameResolver->resolve(Administrator::class);

        return new $entityClassName($data);
    }
}
