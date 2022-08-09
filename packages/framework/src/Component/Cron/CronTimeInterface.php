<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronTimeInterface
{
    /**
     * @return string
     */
    public function getTimeMinutes(): string;

    /**
     * @return string
     */
    public function getTimeHours(): string;
}
