<?php

namespace Shopsys\FrameworkBundle\Component\Cron\Config;

use Shopsys\FrameworkBundle\Component\Cron\CronTimeInterface;

class CronModuleConfig implements CronTimeInterface
{
    /**
     * @var \Shopsys\Plugin\Cron\SimpleCronModuleInterface
     */
    private $service;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $timeMinutes;

    /**
     * @var string
     */
    private $timeHours;

    /**
     * @param \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface $service
     * @param string $serviceId
     * @param string $timeHours
     * @param string $timeMinutes
     */
    public function __construct($service, $serviceId, $timeHours, $timeMinutes)
    {
        $this->service = $service;
        $this->serviceId = $serviceId;
        $this->timeHours = $timeHours;
        $this->timeMinutes = $timeMinutes;
    }

    /**
     * @return \Shopsys\Plugin\Cron\SimpleCronModuleInterface|\Shopsys\Plugin\Cron\IteratedCronModuleInterface
     */
    public function getService()
    {
        return $this->service;
    }

    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getTimeMinutes()
    {
        return $this->timeMinutes;
    }

    public function getTimeHours()
    {
        return $this->timeHours;
    }
}
