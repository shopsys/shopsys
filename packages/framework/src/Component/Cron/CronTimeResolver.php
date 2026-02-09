<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Cron\Config\Exception\InvalidTimeFormatException;

class CronTimeResolver
{
    public function isValidAtTime(CronTimeInterface $cronTime, DateTimeInterface $dateTime): bool
    {
        $hour = (int)$dateTime->format('G');
        $minute = (int)$dateTime->format('i');

        return $this->isMatchWithTimeString($hour, $cronTime->getTimeHours()) &&
            $this->isMatchWithTimeString($minute, $cronTime->getTimeMinutes());
    }

    protected function isMatchWithTimeString(int $value, string $timeString): bool
    {
        $timeValues = explode(',', $timeString);
        $matches = null;

        foreach ($timeValues as $timeValue) {
            if (
                $timeValue === '*'
                || $timeValue === str_pad((string)$value, strlen($timeValue), '0', STR_PAD_LEFT)
                || (preg_match('@^\*/(\d+)$@', $timeValue, $matches)
                    && $value % (int)$matches[1] === 0) // syntax */[int]
            ) {
                return true;
            }
        }

        return false;
    }

    public function validateTimeString(string $timeString, int $maxValue, int $divisibleBy): void
    {
        $timeValues = explode(',', $timeString);
        $matches = null;

        foreach ($timeValues as $timeValue) {
            // syntax */[int]
            if (preg_match('@^\*/(\d+)$@', $timeValue, $matches)) {
                $timeNumber = $matches[1];
            } else {
                $timeNumber = $timeValue;
            }

            if (
                $timeNumber !== '*'
                && !(
                    is_numeric($timeNumber)
                    && $timeNumber <= $maxValue
                    && $timeNumber % $divisibleBy === 0
                )
            ) {
                throw new InvalidTimeFormatException($timeValue, $maxValue, $divisibleBy);
            }
        }
    }
}
