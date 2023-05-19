<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CronModuleFactory implements CronModuleFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $serviceId
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    public function create(string $serviceId): CronModule
    {
        $classData = $this->entityNameResolver->resolve(CronModule::class);

        return new $classData($serviceId);
    }
}
