<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronTimeInterface
{
    public function getTimeMinutes();

    public function getTimeHours();
}
