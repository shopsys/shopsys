<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

interface CronTimeInterface
{
    public function getCronExpression(): string;
}
