<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

class CronModuleFactory implements CronModuleFactoryInterface
{

    public function create(string $serviceId): CronModule
    {
        return new CronModule($serviceId);
    }
}
