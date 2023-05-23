<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PriceExtension extends AbstractExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     * @param \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory
     */
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly Localization $localization,
        protected readonly CurrencyRepositoryInterface $intlCurrencyRepository,
        protected readonly CurrencyFormatterFactory $currencyFormatterFactory,
    ) {
    }

    /**
     * @return array
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'price',
                [$this, 'priceFilter'],
            ),
            new TwigFilter(
                'priceText',
                [$this, 'priceTextFilter'],
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceTextWithCurrencyByCurrencyIdAndLocale',
                [$this, 'priceTextWithCurrencyByCurrencyIdAndLocaleFilter'],
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrency',
                [$this, 'priceWithCurrencyFilter'],
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyAdmin',
                [$this, 'priceWithCurrencyAdminFilter'],
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyByDomainId',
                [$this, 'priceWithCurrencyByDomainIdFilter'],
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyByCurrencyId',
                [$this, 'priceWithCurrencyByCurrencyIdFilter'],
                ['is_safe' => ['html']],
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
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencySymbolDefault',
                [$this, 'getDefaultCurrencySymbol'],
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencySymbolByCurrencyId',
                [$this, 'getCurrencySymbolByCurrencyId'],
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencyCode',
                [$this, 'getCurrencyCodeByDomainId'],
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
        }

        return $this->priceFilter($price);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $currencyId
     * @param string $locale
     * @return string
     */
    public function priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
        Money $price,
        int $currencyId,
        string $locale,
    ): string {
        if ($price->isZero()) {
            return t('Free', [], Translator::DEFAULT_TRANSLATION_DOMAIN, $locale);
        }
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency, $locale);
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
            $locale,
        );

        return $currencyFormatter->format($price->getAmount(), $intlCurrency->getCurrencyCode());
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
