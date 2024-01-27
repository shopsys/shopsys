<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CronModuleRunFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleRun
     */
    public function createFromFinishedCronModule(CronModule $cronModule): CronModuleRun
    {
        $classData = $this->entityNameResolver->resolve(CronModuleRun::class);

        return new $classData(
            $cronModule,
            $cronModule->getStatus(),
            $cronModule->getLastStartedAt(),
            $cronModule->getLastFinishedAt(),
            $cronModule->getLastDuration(),
        );
    }
}
