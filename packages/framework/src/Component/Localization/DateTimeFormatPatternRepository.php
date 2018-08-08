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

    public function add(DateTimeFormatPattern $dateTimePattern)
    {
        $this->dateTimeFormatPatterns[] = $dateTimePattern;
    }

    /**
     * @param string $locale
     * @param int $dateType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function findDateTimePattern($locale, $dateType, $timeType): ?\Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern
    {
        foreach ($this->dateTimeFormatPatterns as $dateTimePattern) {
            if ($this->isMatching($dateTimePattern, $locale, $dateType, $timeType)) {
                return $dateTimePattern;
            }
        }

        return null;
    }

    /**
     * @param string $locale
     * @param int|null $dateType
     * @param int|null $timeType
     */
    private function isMatching(DateTimeFormatPattern $dateTimePattern, $locale, $dateType, $timeType): bool
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
