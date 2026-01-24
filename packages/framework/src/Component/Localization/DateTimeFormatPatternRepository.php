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

    public function add(DateTimeFormatPattern $dateTimePattern): void
    {
        $this->dateTimeFormatPatterns[] = $dateTimePattern;
    }

    /**
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     */
    public function findDateTimePattern(
        string $locale,
        int $dateType,
        int $timeType,
    ): ?DateTimeFormatPattern {
        foreach ($this->dateTimeFormatPatterns as $dateTimePattern) {
            if ($this->isMatching($dateTimePattern, $locale, $dateType, $timeType)) {
                return $dateTimePattern;
            }
        }

        return null;
    }

    protected function isMatching(
        DateTimeFormatPattern $dateTimePattern,
        string $locale,
        ?int $dateType,
        ?int $timeType,
    ): bool {
        if ($dateTimePattern->getLocale() !== $locale) {
            return false;
        }

        if ($dateTimePattern->getDateType() !== null && $dateTimePattern->getDateType() !== $dateType) {
            return false;
        }

        return $dateTimePattern->getTimeType() === null || $dateTimePattern->getTimeType() === $timeType;
    }
}
