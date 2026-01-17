<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Override;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NumberFormatterExtension extends AbstractExtension
{
    protected const MINIMUM_FRACTION_DIGITS = 0;
    protected const MAXIMUM_FRACTION_DIGITS = 10;

    public function __construct(
        protected readonly Localization $localization,
        protected readonly NumberFormatRepositoryInterface $numberFormatRepository,
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
                'formatNumber',
                $this->formatNumber(...),
            ),
            new TwigFilter(
                'formatDecimalNumber',
                $this->formatDecimalNumber(...),
            ),
            new TwigFilter(
                'formatPercent',
                $this->formatPercent(...),
            ),
            new TwigFilter(
                'isInteger',
                $this->isInteger(...),
            ),
        ];
    }

    public function formatNumber(string $number, ?string $locale = null): string
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'decimal',
            'minimum_fraction_digits' => static::MINIMUM_FRACTION_DIGITS,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format($number);
    }

    public function formatDecimalNumber(string $number, int $minimumFractionDigits, ?string $locale = null): string
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'decimal',
            'minimum_fraction_digits' => $minimumFractionDigits,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format($number);
    }

    public function formatPercent(string $number, ?string $locale = null): string
    {
        $numberFormatter = new NumberFormatter($this->numberFormatRepository, [
            'locale' => $this->getLocale($locale),
            'style' => 'percent',
            'minimum_fraction_digits' => static::MINIMUM_FRACTION_DIGITS,
            'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
        ]);

        return $numberFormatter->format((string)((float)$number / 100));
    }

    protected function getLocale(?string $locale = null): string
    {
        if ($locale !== null) {
            return $locale;
        }

        return $this->localization->getRequestLocale();
    }

    public function getName(): string
    {
        return 'number_formatter_extension';
    }

    public function isInteger(string $number): bool
    {
        return is_numeric($number) && (string)(int)$number === (string)$number;
    }
}
