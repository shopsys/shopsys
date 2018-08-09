<?php

namespace Shopsys\FrameworkBundle\Twig;

use DateTime;
use DateTimeImmutable;
use IntlDateFormatter;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig_Extension;

class DateTimeFormatterExtension extends Twig_Extension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatter
     */
    private $dateTimeFormatter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    public function __construct(
        DateTimeFormatter $dateTimeFormatter,
        Localization $localization
    ) {
        $this->dateTimeFormatter = $dateTimeFormatter;
        $this->localization = $localization;
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter(
                'formatDate',
                [$this, 'formatDate']
            ),
            new \Twig_SimpleFilter(
                'formatTime',
                [$this, 'formatTime']
            ),
            new \Twig_SimpleFilter(
                'formatDateTime',
                [$this, 'formatDateTime']
            ),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
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
    private function format($dateTime, $dateType, $timeType, $locale = null)
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
    private function getLocale($locale = null)
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
    private function convertToDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value;
        } elseif ($value instanceof DateTimeImmutable) {
            return new DateTime($value->format(DATE_ISO8601));
        } else {
            return new DateTime($value);
        }
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

    public function getName()
    {
        return 'date_formatter_extension';
    }
}
