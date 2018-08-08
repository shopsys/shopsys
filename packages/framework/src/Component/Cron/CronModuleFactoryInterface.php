<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronModuleFactoryInterface
{
    public function create(string $serviceId): CronModule;
}
