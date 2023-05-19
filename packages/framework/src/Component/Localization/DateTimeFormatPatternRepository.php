<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

class DateTimeFormatPatternRepository
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern[]
     */
    protected array $dateTimeFormatPatterns;

    public function __construct()
    {
        $this->dateTimeFormatPatterns = [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern $dateTimePattern
     */
    public function add(DateTimeFormatPattern $dateTimePattern)
    {
        $this->dateTimeFormatPatterns[] = $dateTimePattern;
    }

    /**
     * @param string $locale
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @return \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern|null
     */
    public function findDateTimePattern($locale, $dateType, $timeType)
    {
        foreach ($this->dateTimeFormatPatterns as $dateTimePattern) {
            if ($this->isMatching($dateTimePattern, $locale, $dateType, $timeType)) {
                return $dateTimePattern;
            }
        }

        return null;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPattern $dateTimePattern
     * @param string $locale
     * @param int|null $dateType
     * @param int|null $timeType
     * @return bool
     */
    protected function isMatching(DateTimeFormatPattern $dateTimePattern, $locale, $dateType, $timeType)
    {
        if ($dateTimePattern->getLocale() !== $locale) {
            return false;
        }

        if ($dateTimePattern->getDateType() !== null && $dateTimePattern->getDateType() !== $dateType) {
            return false;
        }

        return $dateTimePattern->getTimeType() === null || $dateTimePattern->getTimeType() === $timeType;
    }
}
