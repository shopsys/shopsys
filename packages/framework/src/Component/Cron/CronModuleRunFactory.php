<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class CronModuleRunFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function createFromFinishedCronModule(CronModule $cronModule): CronModuleRun
    {
        $entityClassName = $this->entityNameResolver->resolve(CronModuleRun::class);

        return new $entityClassName(
            $cronModule,
            $cronModule->getStatus(),
            $cronModule->getLastStartedAt(),
            $cronModule->getLastFinishedAt(),
            $cronModule->getLastDuration(),
        );
    }
}
