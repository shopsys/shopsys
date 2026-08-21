<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Localization;

use CommerceGuys\Intl\Currency\Currency;
use CommerceGuys\Intl\Currency\CurrencyRepository as BaseCurrencyRepository;
use CommerceGuys\Intl\Exception\UnknownCurrencyException;
use Override;
use Shopsys\FrameworkBundle\Model\Localization\Exception\UndefinedLegacyCurrencyException;
use Shopsys\FrameworkBundle\Model\Localization\Exception\UnsupportedCurrencyException;

class IntlCurrencyRepository extends BaseCurrencyRepository
{
    public const SUPPORTED_CURRENCY_CODES = [
        'AED',
        'AFN',
        'ALL',
        'AMD',
        'AOA',
        'ARS',
        'AUD',
        'AWG',
        'AZN',
        'BAM',
        'BBD',
        'BDT',
        'BHD',
        'BIF',
        'BMD',
        'BND',
        'BOB',
        'BRL',
        'BSD',
        'BTN',
        'BWP',
        'BYN',
        'BZD',
        'CAD',
        'CDF',
        'CHF',
        'CLP',
        'CNY',
        'COP',
        'CRC',
        'CUP',
        'CVE',
        'CZK',
        'DJF',
        'DKK',
        'DOP',
        'DZD',
        'EGP',
        'ERN',
        'ETB',
        'EUR',
        'FJD',
        'FKP',
        'GBP',
        'GEL',
        'GHS',
        'GIP',
        'GMD',
        'GNF',
        'GTQ',
        'GYD',
        'HKD',
        'HNL',
        'HTG',
        'HUF',
        'IDR',
        'ILS',
        'INR',
        'IQD',
        'IRR',
        'ISK',
        'JMD',
        'JOD',
        'JPY',
        'KES',
        'KGS',
        'KHR',
        'KMF',
        'KPW',
        'KRW',
        'KWD',
        'KYD',
        'KZT',
        'LAK',
        'LBP',
        'LKR',
        'LRD',
        'LSL',
        'LYD',
        'MAD',
        'MDL',
        'MGA',
        'MKD',
        'MMK',
        'MNT',
        'MOP',
        'MRU',
        'MUR',
        'MVR',
        'MWK',
        'MXN',
        'MYR',
        'MZN',
        'NAD',
        'NGN',
        'NIO',
        'NOK',
        'NPR',
        'NZD',
        'OMR',
        'PAB',
        'PEN',
        'PGK',
        'PHP',
        'PKR',
        'PLN',
        'PYG',
        'QAR',
        'RON',
        'RSD',
        'RUB',
        'RWF',
        'SAR',
        'SBD',
        'SCR',
        'SDG',
        'SEK',
        'SGD',
        'SHP',
        'SLE',
        'SOS',
        'SRD',
        'SSP',
        'STN',
        'SVC',
        'SYP',
        'SZL',
        'THB',
        'TJS',
        'TMT',
        'TND',
        'TOP',
        'TRY',
        'TTD',
        'TWD',
        'TZS',
        'UAH',
        'UGX',
        'USD',
        'UYU',
        'UYW',
        'UZS',
        'VED',
        'VES',
        'VND',
        'VUV',
        'WST',
        'XAF',
        'XCD',
        'XOF',
        'XPF',
        'YER',
        'ZAR',
        'ZMW',
    ];

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function get(string $currencyCode, ?string $locale = null): Currency
    {
        if (!$this->isSupportedCurrency($currencyCode)) {
            throw new UnsupportedCurrencyException($currencyCode);
        }

        try {
            return parent::get($currencyCode, $locale);
        } catch (UnknownCurrencyException) {
            throw new UndefinedLegacyCurrencyException($currencyCode);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return \CommerceGuys\Intl\Currency\Currency[]
     */
    #[Override]
    public function getAll(?string $locale = null): array
    {
        /** @var \CommerceGuys\Intl\Currency\Currency[] $intlCurrencies */
        $intlCurrencies = parent::getAll($locale);

        $supportedCurrencies = [];

        foreach ($intlCurrencies as $intlCurrency) {
            $currencyCode = $intlCurrency->getCurrencyCode();

            if ($this->isSupportedCurrency($currencyCode)) {
                $supportedCurrencies[$currencyCode] = $intlCurrency;
            }
        }

        return $supportedCurrencies;
    }

    public function isSupportedCurrency(string $currencyCode): bool
    {
        return in_array($currencyCode, self::SUPPORTED_CURRENCY_CODES, true);
    }
}
