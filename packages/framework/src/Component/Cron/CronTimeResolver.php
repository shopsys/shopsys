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

    /**
     * @param int $value
     * @param string $timeString
     */
    private function isMatchWithTimeString($value, $timeString): bool
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

    /**
     * @param string $timeString
     * @param int $maxValue
     * @param int $divisibleBy
     */
    public function validateTimeString($timeString, $maxValue, $divisibleBy)
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
