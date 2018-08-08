<?php

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Twig_Extension;

class NumberFormatterExtension extends Twig_Extension
{
    const MINIMUM_FRACTION_DIGITS = 0;
    const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface
     */
    private $numberFormatRepository;

    public function __construct(
        Localization $localization,
        NumberFormatRepositoryInterface $numberFormatRepository
    ) {
        $this->localization = $localization;
        $this->numberFormatRepository = $numberFormatRepository;
    }

    /**
     * @return \Twig_SimpleFilter[]
     */
    public function getFilters(): array
    {
        return [
            new \Twig_SimpleFilter(
                'formatNumber',
                [$this, 'formatNumber']
            ),
            new \Twig_SimpleFilter(
                'formatDecimalNumber',
                [$this, 'formatDecimalNumber']
            ),
            new \Twig_SimpleFilter(
                'formatPercent',
                [$this, 'formatPercent']
            ),
        ];
    }

    /**
     * @param mixed $number
     * @param string|null $locale
     */
    public function formatNumber($number, $locale = null): string
    {
        $numberFormat = $this->numberFormatRepository->get($this->getLocale($locale));
        $numberFormatter = new NumberFormatter($numberFormat, NumberFormatter::DECIMAL);
        $numberFormatter->setMinimumFractionDigits(self::MINIMUM_FRACTION_DIGITS);
        $numberFormatter->setMaximumFractionDigits(self::MAXIMUM_FRACTION_DIGITS);

        return $numberFormatter->format($number);
    }

    /**
     * @param mixed $number
     * @param int $minimumFractionDigits
     * @param string|null $locale
     */
    public function formatDecimalNumber($number, $minimumFractionDigits, $locale = null): string
    {
        $numberFormat = $this->numberFormatRepository->get($this->getLocale($locale));
        $numberFormatter = new NumberFormatter($numberFormat, NumberFormatter::DECIMAL);
        $numberFormatter->setMinimumFractionDigits($minimumFractionDigits);
        $numberFormatter->setMaximumFractionDigits(self::MAXIMUM_FRACTION_DIGITS);

        return $numberFormatter->format($number);
    }

    /**
     * @param mixed $number
     * @param string|null $locale
     */
    public function formatPercent($number, $locale = null): string
    {
        $numberFormat = $this->numberFormatRepository->get($this->getLocale($locale));
        $numberFormatter = new NumberFormatter($numberFormat, NumberFormatter::PERCENT);
        $numberFormatter->setMinimumFractionDigits(self::MINIMUM_FRACTION_DIGITS);
        $numberFormatter->setMaximumFractionDigits(self::MAXIMUM_FRACTION_DIGITS);

        return $numberFormatter->format($number);
    }

    private function getLocale(?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->localization->getLocale();
        }

        return $locale;
    }

    public function getName(): string
    {
        return 'number_formatter_extension';
    }
}
