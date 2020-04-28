<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CurrencyFormatter;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\CurrencyFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class CurrencyFormatterFactory
{
    public const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @var \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface
     */
    protected $numberFormatRepository;

    /**
     * @var \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface
     */
    protected $intlCurrencyRepository;

    /**
     * @param \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     */
    public function __construct(NumberFormatRepositoryInterface $numberFormatRepository, CurrencyRepositoryInterface $intlCurrencyRepository)
    {
        $this->numberFormatRepository = $numberFormatRepository;
        $this->intlCurrencyRepository = $intlCurrencyRepository;
    }

    /**
     * @param string $locale
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return \CommerceGuys\Intl\Formatter\CurrencyFormatter
     */
    public function createByLocaleAndCurrency(string $locale, Currency $currency): CurrencyFormatter
    {
        $currencyFormatter = new CurrencyFormatter(
            $this->numberFormatRepository,
            $this->intlCurrencyRepository,
            [
                'locale' => $locale,
                'style' => 'standard',
                'minimum_fraction_digits' => $currency->getMinFractionDigits(),
                'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
            ]
        );

        return $currencyFormatter;
    }
}
