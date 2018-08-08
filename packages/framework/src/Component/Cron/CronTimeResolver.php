<?php

namespace Shopsys\FrameworkBundle\Component\Cron;

use DateTimeInterface;

class CronTimeResolver
{
    public function isValidAtTime(CronTimeInterface $cronTime, DateTimeInterface $dateTime): bool
    {
        $hour = (int)$dateTime->format('G');
        $minute = (int)$dateTime->format('i');

        return $this->isMatchWithTimeString($hour, $cronTime->getTimeHours()) &&
            $this->isMatchWithTimeString($minute, $cronTime->getTimeMinutes());
    }

    private function isMatchWithTimeString(int $value, string $timeString): bool
    {
        $timeValues = explode(',', $timeString);
        $matches = null;
        foreach ($timeValues as $timeValue) {
            if ($timeValue === '*'
                || $timeValue === (string)$value
                || preg_match('@^\*/(\d+)$@', $timeValue, $matches) && $value % $matches[1] === 0 // syntax */[int]
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

            if ($timeNumber !== '*'
                && !(is_numeric($timeNumber) && $timeNumber <= $maxValue && $timeNumber % $divisibleBy === 0)
            ) {
                throw new \Shopsys\FrameworkBundle\Component\Cron\Config\Exception\InvalidTimeFormatException($timeValue, $maxValue, $divisibleBy);
            }
        }
    }
}
