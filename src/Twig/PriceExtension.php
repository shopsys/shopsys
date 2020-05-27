<?php

declare(strict_types=1);

namespace App\Twig;

use Shopsys\FrameworkBundle\Twig\PriceExtension as BasePriceExtension;
use Twig\TwigFunction;

/**
 * @property \App\Component\Domain\Domain $domain
 * @method __construct(\Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade, \App\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Localization\Localization $localization, \CommerceGuys\Intl\NumberFormat\NumberFormatRepositoryInterface $numberFormatRepository, \CommerceGuys\Intl\Currency\CurrencyRepositoryInterface $intlCurrencyRepository, \Shopsys\FrameworkBundle\Component\CurrencyFormatter\CurrencyFormatterFactory $currencyFormatterFactory)
 */
class PriceExtension extends BasePriceExtension
{
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
}
