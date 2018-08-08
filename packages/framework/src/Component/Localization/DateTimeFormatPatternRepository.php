<?php

namespace Shopsys\FrameworkBundle\Component\Localization;

class DateTimeFormatPatternRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern[]
     */
    private $dateTimeFormatPatterns;

    public function __construct()
    {
        $this->dateTimeFormatPatterns = [];
    }

    public function add(DateTimeFormatPattern $dateTimePattern): void
    {
        $this->dateTimeFormatPatterns[] = $dateTimePattern;
    }

    /**
     * @param int $dateType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function findDateTimePattern(string $locale, int $dateType, int $timeType): ?\Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern
    {
        foreach ($this->dateTimeFormatPatterns as $dateTimePattern) {
            if ($this->isMatching($dateTimePattern, $locale, $dateType, $timeType)) {
                return $dateTimePattern;
            }
        }

        return null;
    }

    /**
     * @param int|null $dateType
     * @param int|null $timeType
     */
    private function isMatching(DateTimeFormatPattern $dateTimePattern, string $locale, ?int $dateType, ?int $timeType): bool
    {
        if ($dateTimePattern->getLocale() !== $locale) {
            return false;
        }

        if ($dateTimePattern->getDateType() !== null && $dateTimePattern->getDateType() !== $dateType) {
            return false;
        }

        if ($dateTimePattern->getTimeType() !== null && $dateTimePattern->getTimeType() !== $timeType) {
            return false;
        }

        return true;
    }
}
