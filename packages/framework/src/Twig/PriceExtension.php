<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use BadMethodCallException;
use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\Formatter\CurrencyFormatter;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PriceExtension extends AbstractExtension
{
    /**
     * @deprecated
     * moved to {@see \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory}
     */
    protected const MINIMUM_FRACTION_DIGITS = 2;

    /**
     * @deprecated
     * moved to {@see \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory}
     */
    protected const MAXIMUM_FRACTION_DIGITS = 10;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\Localization
     */
    protected $localization;

    /**
     * @var \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface
     */
    protected $numberFormatRepository;

    /**
     * @var \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface
     */
    protected $intlCurrencyRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory|null
     */
    protected $currencyFormatterFactory;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     * @param \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory|null $currencyFormatterFactory
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        Domain $domain,
        Localization $localization,
        NumberFormatRepositoryInterface $numberFormatRepository,
        CurrencyRepositoryInterface $intlCurrencyRepository,
        ?CurrencyFormatterFactory $currencyFormatterFactory = null
    ) {
        $this->currencyFacade = $currencyFacade;
        $this->domain = $domain;
        $this->localization = $localization;
        $this->numberFormatRepository = $numberFormatRepository;
        $this->intlCurrencyRepository = $intlCurrencyRepository;
        $this->currencyFormatterFactory = $currencyFormatterFactory;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory
     */
    public function setCurrencyFormatterFactory(CurrencyFormatterFactory $currencyFormatterFactory)
    {
        if ($this->currencyFormatterFactory !== null && $this->currencyFormatterFactory !== $currencyFormatterFactory) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->currencyFormatterFactory === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->currencyFormatterFactory = $currencyFormatterFactory;
        }
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'price',
                [$this, 'priceFilter']
            ),
            new TwigFilter(
                'priceText',
                [$this, 'priceTextFilter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'priceTextWithCurrencyByCurrencyIdAndLocale',
                [$this, 'priceTextWithCurrencyByCurrencyIdAndLocaleFilter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'priceWithCurrency',
                [$this, 'priceWithCurrencyFilter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'priceWithCurrencyAdmin',
                [$this, 'priceWithCurrencyAdminFilter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'priceWithCurrencyByDomainId',
                [$this, 'priceWithCurrencyByDomainIdFilter'],
                ['is_safe' => ['html']]
            ),
            new TwigFilter(
                'priceWithCurrencyByCurrencyId',
                [$this, 'priceWithCurrencyByCurrencyIdFilter'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'currencySymbolByDomainId',
                [$this, 'getCurrencySymbolByDomainId'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'currencySymbolDefault',
                [$this, 'getDefaultCurrencySymbol'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'currencySymbolByCurrencyId',
                [$this, 'getCurrencySymbolByCurrencyId'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'currencyCode',
                [$this, 'getCurrencyCodeByDomainId']
            ),
        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function priceFilter(Money $price): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function priceTextFilter(Money $price): string
    {
        if ($price->isZero()) {
            return t('Free');
        } else {
            return $this->priceFilter($price);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $currencyId
     * @param string $locale
     * @return string
     */
    public function priceTextWithCurrencyByCurrencyIdAndLocaleFilter(Money $price, int $currencyId, string $locale): string
    {
        if ($price->isZero()) {
            return t('Free');
        } else {
            $currency = $this->currencyFacade->getById($currencyId);
            return $this->formatCurrency($price, $currency, $locale);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @return string
     */
    public function priceWithCurrencyFilter(Money $price, Currency $currency): string
    {
        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function priceWithCurrencyAdminFilter(Money $price): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return string
     */
    public function priceWithCurrencyByDomainIdFilter(Money $price, int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $currencyId
     * @return string
     */
    public function priceWithCurrencyByCurrencyIdFilter(Money $price, int $currencyId): string
    {
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param string|null $locale
     * @return string
     */
    protected function formatCurrency(Money $price, Currency $currency, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->localization->getLocale();
        }

        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($locale, $currency);
        $intlCurrency = $this->intlCurrencyRepository->get(
            $currency->getCode(),
            $locale
        );

        return $currencyFormatter->format($price->getAmount(), $intlCurrency->getCurrencyCode());
    }

    /**
     * @deprecated
     * use create() method of {@see \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory} instead
     * @param string $locale
     * @return \CommerceGuys\Intl\Formatter\CurrencyFormatter
     */
    protected function getCurrencyFormatter(string $locale): CurrencyFormatter
    {
        @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the "CurrencyFormatterFactory::create()" instead.', __METHOD__), E_USER_DEPRECATED);
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
     * @param int $domainId
     * @return string
     */
    public function getCurrencySymbolByDomainId(int $domainId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByDomainIdAndLocale($domainId, $locale);
    }

    /**
     * @param int $domainId
     * @return string
     */
    public function getCurrencyCodeByDomainId(int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $currency->getCode();
    }

    /**
     * @param int $domainId
     * @param string $locale
     * @return string
     */
    protected function getCurrencySymbolByDomainIdAndLocale(int $domainId, string $locale): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    /**
     * @return string
     */
    public function getDefaultCurrencySymbol(): string
    {
        $locale = $this->localization->getLocale();

        return $this->getDefaultCurrencySymbolByLocale($locale);
    }

    /**
     * @param string $locale
     * @return string
     */
    protected function getDefaultCurrencySymbolByLocale(string $locale): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    /**
     * @param int $currencyId
     * @return string
     */
    public function getCurrencySymbolByCurrencyId(int $currencyId): string
    {
        $locale = $this->localization->getLocale();

        return $this->getCurrencySymbolByCurrencyIdAndLocale($currencyId, $locale);
    }

    /**
     * @param int $currencyId
     * @param string $locale
     * @return string
     */
    protected function getCurrencySymbolByCurrencyIdAndLocale(int $currencyId, string $locale): string
    {
        $currency = $this->currencyFacade->getById($currencyId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'price_extension';
    }
}
