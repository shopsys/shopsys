<?php

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTime;
use IntlDateFormatter;

class DateTimeFormatter
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository
     */
    private $customDateTimeFormatPatternRepository;

    public function __construct(DateTimeFormatPatternRepository $customDateTimeFormatPatternRepository)
    {
        $this->customDateTimeFormatPatternRepository = $customDateTimeFormatPatternRepository;
    }

    /**
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @return string|bool
     */
    public function format(DateTime $value, int $dateType, int $timeType, string $locale)
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType,
            null,
            null,
            $this->getCustomPattern($locale, $dateType, $timeType)
        );

        return $intlDateFormatter->format($value);
    }

    private function getCustomPattern(string $locale, ?int $dateType, ?int $timeType): ?string
    {
        $dateTimePattern = $this->customDateTimeFormatPatternRepository->findDateTimePattern($locale, $dateType, $timeType);
        if ($dateTimePattern !== null) {
            $pattern = $dateTimePattern->getPattern();
        } else {
            $pattern = null;
        }

        return $pattern;
    }
}
