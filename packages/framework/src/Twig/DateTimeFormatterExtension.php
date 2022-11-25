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
    /**
     * @var \Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface
     */
    protected $dateTimeFormatter;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

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
    public function getFilters(): array
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
        ];
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    public function getFunctions(): array
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
    public function formatDate(mixed $dateTime, ?string $locale = null): string
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
    public function formatTime(mixed $dateTime, ?string $locale = null): string
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
    public function formatDateTime(mixed $dateTime, ?string $locale = null): string
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
    protected function format(mixed $dateTime, int $dateType, int $timeType, ?string $locale = null): string
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
    protected function getLocale(?string $locale = null): string
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
    protected function convertToDateTime(mixed $value): DateTime
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
    public function dateOfCreation(int $date): string
    {
        $startDate = date('Y', strtotime('1-1-' . $date));
        $endDate = date('Y');

        if ($startDate === $endDate) {
            return $startDate;
        }

        return $startDate . ' - ' . $endDate;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'date_formatter_extension';
    }
}
