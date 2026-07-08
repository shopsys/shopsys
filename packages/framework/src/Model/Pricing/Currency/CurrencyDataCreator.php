<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

/**
 * Creates currencies configured in domains.yaml that are missing in the database (used during application build)
 */
class CurrencyDataCreator
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrencyRepository $currencyRepository,
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly CurrencyDataFactory $currencyDataFactory,
    ) {
    }

    public function createMissingCurrencies(): int
    {
        $createdCurrenciesCount = 0;

        foreach ($this->getAllConfiguredCurrencyCodes() as $currencyCode) {
            if ($this->currencyRepository->findByCode($currencyCode) !== null) {
                continue;
            }

            $this->currencyFacade->create($this->createCurrencyData($currencyCode));
            $createdCurrenciesCount++;
        }

        return $createdCurrenciesCount;
    }

    /**
     * @return string[]
     */
    protected function getAllConfiguredCurrencyCodes(): array
    {
        $currencyCodes = [];

        foreach ($this->domain->getAllIncludingDomainConfigsWithoutDataCreated() as $domainConfig) {
            foreach ($domainConfig->getCurrencyCodes() as $currencyCode) {
                $currencyCodes[$currencyCode] = $currencyCode;
            }
        }

        return array_values($currencyCodes);
    }

    protected function createCurrencyData(string $currencyCode): CurrencyData
    {
        $currencyData = $this->currencyDataFactory->create();
        $currencyData->name = $currencyCode;
        $currencyData->code = $currencyCode;
        $currencyData->exchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
        $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
        $currencyData->roundingType = Currency::DEFAULT_ROUNDING_TYPE;
        $currencyData->roundingPlacesPriceWithoutVat = Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT;

        return $currencyData;
    }
}
