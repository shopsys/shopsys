<?php

namespace Shopsys\FrameworkBundle\Twig;

use DateTime;
use DateTimeImmutable;
use IntlDateFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DateTimeFormatterExtension extends AbstractExtension
{
    protected const HOUR_IN_SECONDS = 60 * 60;

    protected DateTimeFormatterInterface $dateTimeFormatter;

    protected Localization $localization;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface $dateTimeFormatter
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     */
    public function __construct(
        DateTimeFormatterInterface $dateTimeFormatter,
        Localization $localization
    ) {
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->localization = $localization;
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters()
    {
        return [
            new TwigFilter(
                'formatDate',
                [$this, 'formatDate']
            ),
            new TwigFilter(
                'formatTime',
                [$this, 'formatTime']
            ),
            new TwigFilter(
                'formatDateTime',
                [$this, 'formatDateTime']
            ),
            new TwigFilter(
                'formatDurationInSeconds',
                [$this, 'formatDurationInSeconds']
            ),
        ];
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'dateOfCreation',
                [$this, 'dateOfCreation']
            ),
        ];
    }

    /**
     * @param mixed $dateTime
     * @param string|null $locale
     * @return string
     */
    public function formatDate($dateTime, $locale = null)
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $locale
        );
    }

    /**
     * @param mixed $dateTime
     * @param string|null $locale
     * @return string
     */
    public function formatTime($dateTime, $locale = null)
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::NONE,
            IntlDateFormatter::MEDIUM,
            $locale
        );
    }

    /**
     * @param mixed $dateTime
     * @param string|null $locale
     * @return string
     */
    public function formatDateTime($dateTime, $locale = null)
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM,
            $locale
        );
    }

    /**
     * @param mixed $dateTime
     * @param int $dateType {@link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants}
     * @param int $timeType {@link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants}
     * @param string|null $locale
     * @return string
     */
    protected function format($dateTime, $dateType, $timeType, $locale = null)
    {
        if ($dateTime === null) {
            return '-';
        }

        return $this->dateTimeFormatter->format(
            $this->convertToDateTime($dateTime),
            $dateType,
            $timeType,
            $this->getLocale($locale)
        );
    }

    /**
     * @param string|null $locale
     * @return string
     */
    protected function getLocale($locale = null)
    {
        if ($locale === null) {
            $locale = $this->localization->getLocale();
        }

        return $locale;
    }

    /**
     * @param mixed $value
     * @return \DateTime
     */
    protected function convertToDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if ($value instanceof DateTimeImmutable) {
            return new DateTime($value->format(DATE_ISO8601));
        }

        return new DateTime($value);
    }

    /**
     * @param int $date
     * @return string
     */
    public function dateOfCreation($date)
    {
        $startDate = date('Y', strtotime('1-1-' . $date));
        $endDate = date('Y');

        if ($startDate === $endDate) {
            return $startDate;
        }

        return $startDate . ' - ' . $endDate;
    }

    /**
     * @param int|null $durationInSeconds
     * @return string
     */
    public function formatDurationInSeconds(?int $durationInSeconds): string
    {
        if ($durationInSeconds === null) {
            return '';
        }

        $formattedHours = '';

        if ($durationInSeconds >= static::HOUR_IN_SECONDS) {
            $hours = (int)floor($durationInSeconds / static::HOUR_IN_SECONDS);
            $formattedHours .= $hours . ':';

            $durationInSeconds -= $hours * static::HOUR_IN_SECONDS;
        }

        return $formattedHours . date('i:s', $durationInSeconds);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'date_formatter_extension';
    }
}
