<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronModuleFactoryInterface
{
    /**
     * @param string $serviceId
     * @return \Shopsys\FrameworkBundle\Component\Cron\CronModule
     */
    public function create(string $serviceId): CronModule;
}
