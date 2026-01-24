<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use DateTimeInterface;
use IntlDateFormatter;
use Override;
use Shopsys\FrameworkBundle\Component\Localization\DateTimeFormatterInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Symfony\Component\Clock\DatePoint;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class DateTimeFormatterExtension extends AbstractExtension
{
    protected const HOUR_IN_SECONDS = 60 * 60;

    public function __construct(
        protected readonly DateTimeFormatterInterface $dateTimeFormatter,
        protected readonly Localization $localization,
    ) {
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'formatDate',
                $this->formatDate(...),
            ),
            new TwigFilter(
                'formatTime',
                $this->formatTime(...),
            ),
            new TwigFilter(
                'formatDateTime',
                $this->formatDateTime(...),
            ),
            new TwigFilter(
                'formatDurationInSeconds',
                $this->formatDurationInSeconds(...),
            ),
        ];
    }

    /**
     * @return \Twig\TwigFunction[]
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'dateOfCreation',
                $this->dateOfCreation(...),
            ),
        ];
    }

    public function formatDate(mixed $dateTime, ?string $locale = null): string
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::NONE,
            $locale,
        );
    }

    public function formatTime(mixed $dateTime, ?string $locale = null): string
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::NONE,
            IntlDateFormatter::MEDIUM,
            $locale,
        );
    }

    public function formatDateTime(mixed $dateTime, ?string $locale = null): string
    {
        return $this->format(
            $dateTime,
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::MEDIUM,
            $locale,
        );
    }

    /**
     * @param int $dateType {@link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants}
     * @param int $timeType {@link http://php.net/manual/en/class.intldateformatter.php#intl.intldateformatter-constants}
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
            $this->getLocale($locale),
        );
    }

    protected function getLocale(?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->localization->getRequestLocale();
        }

        return $locale;
    }

    protected function convertToDateTime(mixed $value): DateTimeInterface
    {
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        return new DatePoint($value);
    }

    public function dateOfCreation(int $date): string
    {
        $startDate = date('Y', strtotime('1-1-' . $date));
        $endDate = date('Y');

        if ($startDate === $endDate) {
            return $startDate;
        }

        return $startDate . ' - ' . $endDate;
    }

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

    public function getName(): string
    {
        return 'date_formatter_extension';
    }
}
