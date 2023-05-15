<?php

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTime;
use IntlDateFormatter;

class DateTimeFormatter implements DateTimeFormatterInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository $customDateTimeFormatPatternRepository
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     */
    public function __construct(
        protected readonly DateTimeFormatPatternRepository $customDateTimeFormatPatternRepository,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
    ) {
    }

    /**
     * @param \DateTime $value
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param string|null $locale
     * @return string|bool
     */
    public function format(DateTime $value, $dateType, $timeType, $locale)
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType,
            $this->displayTimeZoneProvider->getDisplayTimeZone(),
            null,
            $this->getCustomPattern($locale, $dateType, $timeType),
        );

        return $intlDateFormatter->format($value);
    }

    /**
     * @param string $locale
     * @param int|null $dateType
     * @param int|null $timeType
     * @return string|null
     */
    protected function getCustomPattern($locale, $dateType, $timeType)
    {
        $dateTimePattern = $this->customDateTimeFormatPatternRepository->findDateTimePattern(
            $locale,
            $dateType,
            $timeType,
        );

        if ($dateTimePattern !== null) {
            $pattern = $dateTimePattern->getPattern();
        } else {
            $pattern = null;
        }

        return $pattern;
    }
}
