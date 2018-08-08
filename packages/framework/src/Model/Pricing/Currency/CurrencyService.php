<?php

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;

class CurrencyService
{

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFactoryInterface
     */
    protected $currencyFactory;

    public function __construct(CurrencyFactoryInterface $currencyFactory)
    {
        $this->currencyFactory = $currencyFactory;
    }

    public function create(CurrencyData $currencyData): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        return $this->currencyFactory->create($currencyData);
    }

    public function edit(Currency $currency, CurrencyData $currencyData, bool $isDefaultCurrency): \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency
    {
        $currency->edit($currencyData);
        if ($isDefaultCurrency) {
            $currency->setExchangeRate(Currency::DEFAULT_EXCHANGE_RATE);
        } else {
            $currency->setExchangeRate($currencyData->exchangeRate);
        }

        return $currency;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency[] $currenciesUsedInOrders
     * @return int[]
     */
    public function getNotAllowedToDeleteCurrencyIds(
        int $defaultCurrencyId,
        array $currenciesUsedInOrders,
        PricingSetting $pricingSetting,
        Domain $domain
    ): array {
        $notAllowedToDeleteCurrencyIds = [$defaultCurrencyId];
        foreach ($domain->getAll() as $domainConfig) {
            $notAllowedToDeleteCurrencyIds[] = $pricingSetting->getDomainDefaultCurrencyIdByDomainId($domainConfig->getId());
        }
        foreach ($currenciesUsedInOrders as $currency) {
            $notAllowedToDeleteCurrencyIds[] = $currency->getId();
        }

        return array_unique($notAllowedToDeleteCurrencyIds);
    }
}
