<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CurrencyFormatter;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\CurrencyFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;

class CurrencyFormatterFactory
{
    /**
     * @deprecated Will be removed in the next major release, this constant can be edited by admin
     */
    public const MINIMUM_FRACTION_DIGITS = 2;
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
     * @deprecated Will be removed in the next major release, use CurrencyFormatterFactory::createByLocaleAndCurrency instead
     *
     * @param string $locale
     * @return \CommerceGuys\Intl\Formatter\CurrencyFormatter
     */
    public function create(string $locale): CurrencyFormatter
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the CurrencyFormatterFactory::createByLocaleAndCurrency instead.', __METHOD__), E_USER_DEPRECATED);

        $currencyFormatter = new CurrencyFormatter(
            $this->numberFormatRepository,
            $this->intlCurrencyRepository,
            [
                'locale' => $locale,
                'style' => 'standard',
                'minimum_fraction_digits' => static::MINIMUM_FRACTION_DIGITS,
                'maximum_fraction_digits' => static::MAXIMUM_FRACTION_DIGITS,
            ]
        );

        return $currencyFormatter;
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
