<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NumberFormatterExtension extends AbstractExtension
{
    protected const MINIMUM_FRACTION_DIGITS = 0;
    protected const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository
     * @param \Shopsys\FrameworkBundle\Model\Administration\AdministrationFacade $administrationFacade
     */
    public function __construct(
        protected readonly Localization $localization,
        protected readonly NumberFormatRepositoryInterface $numberFormatRepository,
        protected readonly AdministrationFacade $administrationFacade,
    ) {
    }

    /**
     * @return \Twig\TwigFilter[]
     */
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

    /**
     * @param string $number
     * @param string|null $locale
     * @return string
     */
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

    /**
     * @param string $number
     * @param int $minimumFractionDigits
     * @param string|null $locale
     * @return string
     */
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

    /**
     * @param string $number
     * @param string|null $locale
     * @return string
     */
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

    /**
     * @param string|null $locale
     * @return string
     */
    protected function getLocale(?string $locale = null): string
    {
        if ($locale !== null) {
            return $locale;
        }

        if ($this->administrationFacade->isInAdmin()) {
            return $this->localization->getAdminLocale();
        }

        return $this->localization->getLocale();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'number_formatter_extension';
    }

    /**
     * @param string $number
     * @return bool
     */
    public function isInteger(string $number): bool
    {
        return is_numeric($number) && (string)(int)$number === (string)$number;
    }
}
