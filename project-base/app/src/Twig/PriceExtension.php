<?php

declare(strict_types=1);

namespace App\Twig;

use CommerceGuys\Intl\Currency\CurrencyRepositoryInterface;
use Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Localization\Localization;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\TwigFilter;

/**
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 */
class PriceExtension extends BasePriceExtension
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Localization\Localization $localization
     * @param \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository
     * @param \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory
     * @param \Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade $adminDomainTabsFacade
     */
    public function __construct(
        CurrencyFacade $currencyFacade,
        Domain $domain,
        Localization $localization,
        CurrencyRepositoryInterface $intlCurrencyRepository,
        CurrencyFormatterFactory $currencyFormatterFactory,
        private AdminDomainTabsFacade $adminDomainTabsFacade,
    ) {
        parent::__construct(
            $currencyFacade,
            $domain,
            $localization,
            $intlCurrencyRepository,
            $currencyFormatterFactory,
        );
    }

    /**
     * @return \Twig\TwigFilter[]
     */
    public function getFilters(): array
    {
        $filters = parent::getFilters();

        $filters[] = new TwigFilter(
            'priceFromDecimalStringWithCurrencyAdmin',
            [$this, 'priceFromDecimalStringWithCurrencyAdmin'],
        );

        return $filters;
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
}
