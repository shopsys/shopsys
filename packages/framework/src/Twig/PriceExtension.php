<?php

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\NumberFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Twig_Extension;
use Twig_SimpleFilter;
use Twig_SimpleFunction;

class PriceExtension extends Twig_Extension
{
    const MINIMUM_FRACTION_DIGITS = 2;
    const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    private $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    private $localization;

    /**
     * @var \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface
     */
    private $numberFormatRepository;

    /**
     * @var \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface
     */
    private $intlCurrencyRepository;

    public function __construct(
        CurrencyFacade $currencyFacade,
        Domain $domain,
        Localization $localization,
        NumberFormatRepositoryInterface $numberFormatRepository,
        CurrencyRepositoryInterface $intlCurrencyRepository
    ) {
        $this->currencyFacade = $currencyFacade;
        $this->domain = $domain;
        $this->localization = $localization;
        $this->numberFormatRepository = $numberFormatRepository;
        $this->intlCurrencyRepository = $intlCurrencyRepository;
    }
    
    public function getFilters()
    {
        return [
            new Twig_SimpleFilter(
                'price',
                [$this, 'priceFilter']
            ),
            new Twig_SimpleFilter(
                'priceText',
                [$this, 'priceTextFilter'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'priceTextWithCurrencyByCurrencyIdAndLocale',
                [$this, 'priceTextWithCurrencyByCurrencyIdAndLocaleFilter'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'priceWithCurrency',
                [$this, 'priceWithCurrencyFilter'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'priceWithCurrencyAdmin',
                [$this, 'priceWithCurrencyAdminFilter'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'priceWithCurrencyByDomainId',
                [$this, 'priceWithCurrencyByDomainIdFilter'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFilter(
                'priceWithCurrencyByCurrencyId',
                [$this, 'priceWithCurrencyByCurrencyIdFilter'],
                ['is_safe' => ['html']]
            ),
        ];
    }
    
    public function getFunctions()
    {
        return [
            new Twig_SimpleFunction(
                'currencySymbolByDomainId',
                [$this, 'getCurrencySymbolByDomainId'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'currencySymbolDefault',
                [$this, 'getDefaultCurrencySymbol'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'currencySymbolByCurrencyId',
                [$this, 'getCurrencySymbolByCurrencyId'],
                ['is_safe' => ['html']]
            ),
            new Twig_SimpleFunction(
                'currencyCode',
                [$this, 'getCurrencyCodeByDomainId']
            ),
        ];
    }

    /**
     * @param string $price
     */
    public function priceFilter($price): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param string $price
     */
    public function priceTextFilter($price): string
    {
        if ($price == 0) {
            return t('Free');
        } else {
            return $this->priceFilter($price);
        }
    }

    /**
     * @param string $price
     * @param int $currencyId
     * @param string $locale
     */
    public function priceTextWithCurrencyByCurrencyIdAndLocaleFilter($price, $currencyId, $locale): string
    {
        if ($price == 0) {
            return t('Free');
        } else {
            $currency = $this->currencyFacade->getById($currencyId);
            return $this->formatCurrency($price, $currency, $locale);
        }
    }

    /**
     * @param string $price
     */
    public function priceWithCurrencyFilter($price, Currency $currency): string
    {
        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param string $price
     */
    public function priceWithCurrencyAdminFilter($price): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param string $price
     * @param int $domainId
     */
    public function priceWithCurrencyByDomainIdFilter($price, $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyByCurrencyIdFilter($price, $currencyId)
    {
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param string $price
     * @param string|null $locale
     */
    private function formatCurrency($price, Currency $currency, $locale = null): string
    {
        if (!is_numeric($price)) {
            return $price;
        }
        if ($locale === null) {
            $locale = $this->localization->getLocale();
        }

        $numberFormatter = $this->getNumberFormatter($locale);
        $intlCurrency = $this->intlCurrencyRepository->get(
            $currency->getCode(),
            $locale
        );

        // $price can be float so we round it before formatting to overcome floating point errors.
        // If the amounts will be 10^9 or less, the errors should not be in the first 6 decimal places.
        $priceWithFixedFloatingPointError = round($price, 6);

        return $numberFormatter->formatCurrency($priceWithFixedFloatingPointError, $intlCurrency);
    }

    /**
     * @param string $locale
     */
    private function getNumberFormatter($locale): \CommerceGuys\Intl\Formatter\NumberFormatter
    {
        $numberFormat = $this->numberFormatRepository->get($locale);
        $numberFormatter = new NumberFormatter($numberFormat, NumberFormatter::CURRENCY);
        $numberFormatter->setMinimumFractionDigits(self::MINIMUM_FRACTION_DIGITS);
        $numberFormatter->setMaximumFractionDigits(self::MAXIMUM_FRACTION_DIGITS);

        return $numberFormatter;
    }

    /**
     * @param int $domainId
     */
    public function getCurrencySymbolByDomainId($domainId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByDomainIdAndLocale($domainId, $locale);
    }

    /**
     * @param int $domainId
     */
    public function getCurrencyCodeByDomainId($domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $currency->getCode();
    }

    /**
     * @param int $domainId
     * @param string $locale
     */
    private function getCurrencySymbolByDomainIdAndLocale($domainId, $locale): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    public function getDefaultCurrencySymbol(): string
    {
        $locale = $this->localization->getLocale();

        return $this->getDefaultCurrencySymbolByLocale($locale);
    }

    /**
     * @param string $locale
     */
    private function getDefaultCurrencySymbolByLocale($locale): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    /**
     * @param int $currencyId
     */
    public function getCurrencySymbolByCurrencyId($currencyId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByCurrencyIdAndLocale($currencyId, $locale);
    }

    /**
     * @param int $currencyId
     * @param string $locale
     */
    private function getCurrencySymbolByCurrencyIdAndLocale($currencyId, $locale): string
    {
        $currency = $this->currencyFacade->getById($currencyId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    public function getName(): string
    {
        return 'price_extension';
    }
}
