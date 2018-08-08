<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronTimeInterface
{
    public function getTimeMinutes(): string;

    public function getTimeHours(): string;
}
