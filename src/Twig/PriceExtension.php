<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * @property \App\Component\Domain\Domain $domain
 * @method __construct(\Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository, \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository, \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory)
 */
class PriceExtension extends BasePriceExtension
{
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
