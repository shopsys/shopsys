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
    
    public function priceFilter(string $price): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->formatCurrency($price, $currency);
    }
    
    public function priceTextFilter(string $price): string
    {
        if ($price == 0) {
            return t('Free');
        } else {
            return $this->priceFilter($price);
        }
    }
    
    public function priceTextWithCurrencyByCurrencyIdAndLocaleFilter(string $price, int $currencyId, string $locale): string
    {
        if ($price == 0) {
            return t('Free');
        } else {
            $currency = $this->currencyFacade->getById($currencyId);
            return $this->formatCurrency($price, $currency, $locale);
        }
    }
    
    public function priceWithCurrencyFilter(string $price, Currency $currency): string
    {
        return $this->formatCurrency($price, $currency);
    }
    
    public function priceWithCurrencyAdminFilter(string $price): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();

        return $this->formatCurrency($price, $currency);
    }
    
    public function priceWithCurrencyByDomainIdFilter(string $price, int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyByCurrencyIdFilter($price, $currencyId)
    {
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency);
    }

    private function formatCurrency(string $price, Currency $currency, ?string $locale = null): string
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
    
    private function getNumberFormatter(string $locale): \CommerceGuys\Intl\Formatter\NumberFormatter
    {
        $numberFormat = $this->numberFormatRepository->get($locale);
        $numberFormatter = new NumberFormatter($numberFormat, NumberFormatter::CURRENCY);
        $numberFormatter->setMinimumFractionDigits(self::MINIMUM_FRACTION_DIGITS);
        $numberFormatter->setMaximumFractionDigits(self::MAXIMUM_FRACTION_DIGITS);

        return $numberFormatter;
    }
    
    public function getCurrencySymbolByDomainId(int $domainId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByDomainIdAndLocale($domainId, $locale);
    }
    
    public function getCurrencyCodeByDomainId(int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $currency->getCode();
    }
    
    private function getCurrencySymbolByDomainIdAndLocale(int $domainId, string $locale): string
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
    
    private function getDefaultCurrencySymbolByLocale(string $locale): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }
    
    public function getCurrencySymbolByCurrencyId(int $currencyId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByCurrencyIdAndLocale($currencyId, $locale);
    }
    
    private function getCurrencySymbolByCurrencyIdAndLocale(int $currencyId, string $locale): string
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
