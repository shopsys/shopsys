<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\CurrencyFormatter;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\CurrencyFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;

class CurrencyFormatterFactory
{
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
     * @param string $locale
     * @return \CommerceGuys\Intl\Formatter\CurrencyFormatter
     */
    public function create(string $locale): CurrencyFormatter
    {
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
}
