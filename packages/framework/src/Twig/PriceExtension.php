<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Twig;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Override;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class PriceExtension extends AbstractExtension
{
    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
        protected readonly Localization $localization,
        protected readonly CurrencyRepositoryInterface $intlCurrencyRepository,
        protected readonly CurrencyFormatterFactory $currencyFormatterFactory,
        protected readonly AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
    }

    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter(
                'price',
                $this->priceFilter(...),
            ),
            new TwigFilter(
                'priceText',
                $this->priceTextFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceTextWithCurrencyByCurrencyIdAndLocale',
                $this->priceTextWithCurrencyByCurrencyIdAndLocaleFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrency',
                $this->priceWithCurrencyFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyAdmin',
                $this->priceWithCurrencyAdminFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyByDomainId',
                $this->priceWithCurrencyByDomainIdFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceWithCurrencyByCurrencyId',
                $this->priceWithCurrencyByCurrencyIdFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceFromDecimalStringWithCurrencyAdmin',
                $this->priceFromDecimalStringWithCurrencyAdmin(...),
            ),
            new TwigFilter(
                'priceWithCurrencyByOrder',
                $this->priceWithCurrencyByOrderFilter(...),
                ['is_safe' => ['html']],
            ),
            new TwigFilter(
                'priceTextWithCurrencyByOrderAndLocale',
                $this->priceTextWithCurrencyByOrderAndLocaleFilter(...),
                ['is_safe' => ['html']],
            ),
        ];
    }

    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'currencySymbolByDomainId',
                $this->getCurrencySymbolByDomainId(...),
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencySymbolDefault',
                $this->getDefaultCurrencySymbol(...),
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencySymbolByCurrencyId',
                $this->getCurrencySymbolByCurrencyId(...),
                ['is_safe' => ['html']],
            ),
            new TwigFunction(
                'currencyCode',
                $this->getCurrencyCodeByDomainId(...),
            ),
            new TwigFunction(
                'currencySymbolByCode',
                $this->getCurrencySymbolByCode(...),
                ['is_safe' => ['html']],
            ),
        ];
    }

    public function priceFilter(Money $price): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->formatCurrency($price, $currency);
    }

    public function priceTextFilter(Money $price): string
    {
        if ($price->isZero()) {
            return t('Free', domain: Translator::CUSTOMER_TRANSLATION_DOMAIN);
        }

        return $this->priceFilter($price);
    }

    public function priceTextWithCurrencyByCurrencyIdAndLocaleFilter(
        Money $price,
        int $currencyId,
        string $locale,
    ): string {
        if ($price->isZero()) {
            return t('Free', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $locale);
        }
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency, $locale);
    }

    public function priceWithCurrencyFilter(Money $price, Currency $currency): string
    {
        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyAdminFilter(Money $price): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();

        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyByDomainIdFilter(Money $price, int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyByCurrencyIdFilter(Money $price, int $currencyId): string
    {
        $currency = $this->currencyFacade->getById($currencyId);

        return $this->formatCurrency($price, $currency);
    }

    public function priceWithCurrencyByOrderFilter(Money $price, Order $order): string
    {
        return $this->formatCurrencyByFrozenValues($price, $order->getCurrencyCode(), $order->getCurrencyMinFractionDigits());
    }

    public function priceTextWithCurrencyByOrderAndLocaleFilter(Money $price, Order $order, string $locale): string
    {
        if ($price->isZero()) {
            return t('Free', [], Translator::CUSTOMER_TRANSLATION_DOMAIN, $locale);
        }

        return $this->formatCurrencyByFrozenValues($price, $order->getCurrencyCode(), $order->getCurrencyMinFractionDigits(), $locale);
    }

    protected function formatCurrency(Money $price, Currency $currency, ?string $locale = null): string
    {
        return $this->formatCurrencyByFrozenValues($price, $currency->getCode(), $currency->getMinFractionDigits(), $locale);
    }

    protected function formatCurrencyByFrozenValues(
        Money $price,
        string $currencyCode,
        int $minFractionDigits,
        ?string $locale = null,
    ): string {
        if ($locale === null) {
            $locale = $this->localization->getRequestLocale();
        }

        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndMinFractionDigits($locale, $minFractionDigits);
        $intlCurrency = $this->intlCurrencyRepository->get($currencyCode, $locale);

        return $currencyFormatter->format($price->getAmount(), $intlCurrency->getCurrencyCode());
    }

    public function getCurrencySymbolByDomainId(int $domainId): string
    {
        $locale = $this->localization->getRequestLocale();

        return $this->getCurrencySymbolByDomainIdAndLocale($domainId, $locale);
    }

    public function getCurrencyCodeByDomainId(int $domainId): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $currency->getCode();
    }

    protected function getCurrencySymbolByDomainIdAndLocale(int $domainId, string $locale): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    public function getDefaultCurrencySymbol(): string
    {
        $locale = $this->localization->getRequestLocale();

        return $this->getDefaultCurrencySymbolByLocale($locale);
    }

    protected function getDefaultCurrencySymbolByLocale(string $locale): string
    {
        $currency = $this->currencyFacade->getDefaultCurrency();
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    public function getCurrencySymbolByCurrencyId(int $currencyId): string
    {
        $locale = $this->localization->getRequestLocale();

        return $this->getCurrencySymbolByCurrencyIdAndLocale($currencyId, $locale);
    }

    public function priceFromDecimalStringWithCurrencyAdmin(string $priceDecimal): string
    {
        $money = Money::create($priceDecimal);
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $this->priceWithCurrencyByDomainIdFilter($money, $domainId);
    }

    protected function getCurrencySymbolByCurrencyIdAndLocale(int $currencyId, string $locale): string
    {
        $currency = $this->currencyFacade->getById($currencyId);
        $intlCurrency = $this->intlCurrencyRepository->get($currency->getCode(), $locale);

        return $intlCurrency->getSymbol();
    }

    public function getCurrencySymbolByCode(string $currencyCode): string
    {
        $locale = $this->localization->getRequestLocale();
        $intlCurrency = $this->intlCurrencyRepository->get($currencyCode, $locale);

        return $intlCurrency->getSymbol();
    }

    public function getName(): string
    {
        return 'price_extension';
    }
}
