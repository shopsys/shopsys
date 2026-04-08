<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use Cron\CronExpression;
use DateTimeInterface;

class CronTimeResolver
{
    public function isValidAtTime(CronTimeInterface $cronTime, DateTimeInterface $dateTime): bool
    {
        return $this
            ->createCronExpression($cronTime->getCronExpression())
            ->isDue($dateTime);
    }

    public function validateCronExpression(string $cronExpression): void
    {
        // CronExpression constructor throws an exception if the expression is invalid
        $this->createCronExpression($cronExpression);
    }

    protected function createCronExpression(string $cronExpression): CronExpression
    {
        return new CronExpression($cronExpression);
    }
}
