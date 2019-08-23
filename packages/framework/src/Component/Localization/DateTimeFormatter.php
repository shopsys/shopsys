<?php

namespace Shopsys\FrameworkBundle\Component\Localization;

use BadMethodCallException;
use DateTime;
use IntlDateFormatter;

class DateTimeFormatter implements DateTimeFormatterInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository
     */
    protected $customDateTimeFormatPatternRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface|null
     */
    protected $displayTimeZoneProvider;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatPatternRepository $customDateTimeFormatPatternRepository
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface|null $displayTimeZoneProvider
     */
    public function __construct(
        DateTimeFormatPatternRepository $customDateTimeFormatPatternRepository,
        ?DisplayTimeZoneProviderInterface $displayTimeZoneProvider = null
    ) {
        $this->customDateTimeFormatPatternRepository = $customDateTimeFormatPatternRepository;
        $this->displayTimeZoneProvider = $displayTimeZoneProvider;
    }

    /**
     * @required
     * @param \Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface $displayTimeZoneProvider
     * @internal This function will be replaced by constructor injection in next major
     */
    public function setDisplayTimeZoneProvider(DisplayTimeZoneProviderInterface $displayTimeZoneProvider): void
    {
        if ($this->displayTimeZoneProvider !== null && $this->displayTimeZoneProvider !== $displayTimeZoneProvider) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }

        if ($this->displayTimeZoneProvider === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->displayTimeZoneProvider = $displayTimeZoneProvider;
        }
    }

    /**
     * @param \DateTime $value
     * @param int $dateType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param int $timeType @see http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants
     * @param string $locale
     * @return string|bool
     */
    public function format(DateTime $value, $dateType, $timeType, $locale)
    {
        $intlDateFormatter = new IntlDateFormatter(
            $locale,
            $dateType,
            $timeType,
            $this->displayTimeZoneProvider !== null ? $this->displayTimeZoneProvider->getDisplayTimeZone() : null,
            null,
            $this->getCustomPattern($locale, $dateType, $timeType)
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
        $dateTimePattern = $this->customDateTimeFormatPatternRepository->findDateTimePattern($locale, $dateType, $timeType);
        if ($dateTimePattern !== null) {
            $pattern = $dateTimePattern->getPattern();
        } else {
            $pattern = null;
        }

        return $pattern;
    }
}
