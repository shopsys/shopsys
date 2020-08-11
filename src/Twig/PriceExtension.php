<?php

declare(strict_types=1);

namespace App\Twig;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @property \App\Component\Domain\Domain $domain
 */
class PriceExtension extends BasePriceExtension
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade
     */
    private AdminDomainTabsFacade $adminDomainTabsFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     * @param \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        Domain $domain,
        Localization $localization,
        NumberFormatRepositoryInterface $numberFormatRepository,
        CurrencyRepositoryInterface $intlCurrencyRepository,
        CurrencyFormatterFactory $currencyFormatterFactory,
        AdminDomainTabsFacade $adminDomainTabsFacade
    ) {
        parent::__construct(
            $currencyFacade,
            $domain,
            $localization,
            $numberFormatRepository,
            $intlCurrencyRepository,
            $currencyFormatterFactory
        );

        $this->adminDomainTabsFacade = $adminDomainTabsFacade;
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        $filters = parent::getFilters();

        $filters[] = new TwigFilter(
            'priceWithoutCurrency',
            [$this, 'priceWithoutCurrencyFilter']
        );
        $filters[] = new TwigFilter(
            'priceFromDecimalStringWithCurrencyAdmin',
            [$this, 'priceFromDecimalStringWithCurrencyAdmin']
        );

        return $filters;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        $functions = parent::getFunctions();

        $functions[] = new TwigFunction(
            'decimalsByDomainId',
            [$this, 'getDecimalsByDomainId'],
            ['is_safe' => ['html']]
        );

        return $functions;
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getDecimalsByDomainId(int $domainId): int
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

        return $currency->getMinFractionDigits();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @return string
     */
    public function priceWithoutCurrencyFilter(Money $price): string
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return $this->formatCurrencyWithoutSymbol($price, $currency);
    }

    /**
     * @param string $priceDecimal
     * @return string
     */
    public function priceFromDecimalStringWithCurrencyAdmin(string $priceDecimal): string
    {
        $money = Money::create($priceDecimal);
        $domainId = $this->adminDomainTabsFacade->getSelectedDomainId();

        return $this->priceWithCurrencyByDomainIdFilter($money, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param string|null $locale
     * @return string
     */
    protected function formatCurrencyWithoutSymbol(Money $price, Currency $currency, ?string $locale = null): string
    {
        if ($locale === null) {
            $locale = $this->localization->getLocale();
        }

        $currencyFormatter = $this->currencyFormatterFactory->createByLocaleAndCurrency($locale, $currency);
        $intlCurrency = $this->intlCurrencyRepository->get(
            $currency->getCode(),
            $locale
        );

        $options = ['currency_display' => 'none'];

        return $currencyFormatter->format($price->getAmount(), $intlCurrency->getCurrencyCode(), $options);
    }
}
