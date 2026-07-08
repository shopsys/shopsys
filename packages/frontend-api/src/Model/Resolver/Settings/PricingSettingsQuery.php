<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PricingSettingsQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly PricingSetting $pricingSetting,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    /**
     * @return array{defaultCurrencyCode: string, currentCurrencyCode: string, minimumFractionDigits: int, availableCurrencies: array<int, array{code: string, name: string, minFractionDigits: int}>}
     */
    public function pricingSettingsQuery(): array
    {
        $domainId = $this->domain->getId();
        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);
        $currentCurrency = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($domainId);

        return [
            'defaultCurrencyCode' => $defaultCurrency->getCode(),
            'currentCurrencyCode' => $currentCurrency->getCode(),
            'minimumFractionDigits' => $currentCurrency->getMinFractionDigits(),
            'sellingPriceType' => $this->pricingSetting->getSellingPriceType(),
            'availableCurrencies' => array_map(
                static fn (Currency $currency): array => [
                    'code' => $currency->getCode(),
                    'name' => $currency->getName(),
                    'minFractionDigits' => $currency->getMinFractionDigits(),
                ],
                $this->currencyFacade->getEnabledCurrenciesByDomainId($domainId),
            ),
        ];
    }
}
