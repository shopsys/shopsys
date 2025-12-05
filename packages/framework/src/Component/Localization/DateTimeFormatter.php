<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Localization;

use DateTimeInterface;
use IntlDateFormatter;
use Override;

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
     * @param \DateTimeInterface $value
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param string|null $locale
     * @return string|false
     */
    #[Override]
    public function format(DateTimeInterface $value, $dateType, $timeType, $locale): string|false
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType,
            $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin(),
            null,
            $this->getCustomPattern($locale, $dateType, $timeType),
        );

        return $intlDateFormatter->format($value);
    }

    /**
     * @param string|null $locale
     * @param int|null $dateType
     * @param int|null $timeType
     * @return string|null
     */
    protected function getCustomPattern(?string $locale, ?int $dateType, ?int $timeType): ?string
    {
        $dateTimePattern = $this->customDateTimeFormatPatternRepository->findDateTimePattern(
            $locale,
            $dateType,
            $timeType,
        );

        return $dateTimePattern?->getPattern();
    }
}
