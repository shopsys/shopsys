<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

class CronModuleRunFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Cron\CronModule $cronModule
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModuleRun
     */
    public function createFromFinishedCronModule(CronModule $cronModule): CronModuleRun
    {
        return new CronModuleRun(
            $cronModule,
            $cronModule->getStatus(),
            $cronModule->getLastStartedAt(),
            $cronModule->getLastFinishedAt(),
            $cronModule->getLastDuration(),
        );
    }
}
