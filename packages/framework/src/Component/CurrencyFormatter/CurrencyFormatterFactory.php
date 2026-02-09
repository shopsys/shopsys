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

    public function __construct(
        protected readonly NumberFormatRepositoryInterface $numberFormatRepository,
        protected readonly CurrencyRepositoryInterface $intlCurrencyRepository,
    ) {
    }

    public function createByLocaleAndCurrency(string $locale, Currency $currency): CurrencyFormatter
    {
        return new CurrencyFormatter(
            $this->numberFormatRepository,
            $this->intlCurrencyRepository,
            [
                'locale' => $locale,
                'style' => 'standard',
                'minimum_fraction_digits' => $currency->getMinFractionDigits(),
                'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
            ],
        );
    }
}
