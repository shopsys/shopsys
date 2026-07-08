<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Pricing\Currency;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

/**
 * Holds the currency selected for the current request or batch scope (feed export, Elasticsearch export, ...).
 * When no currency is set (or the set one is not enabled on the given domain), the domain default currency is used.
 */
class CurrentCurrencyProvider
{
    protected ?string $currentCurrencyCode = null;

    public function __construct(
        protected readonly CurrencyFacade $currencyFacade,
        protected readonly Domain $domain,
    ) {
    }

    public function setCurrentCurrencyCode(?string $currencyCode): void
    {
        $this->currentCurrencyCode = $currencyCode;
    }

    public function getCurrentCurrencyCode(): ?string
    {
        return $this->currentCurrencyCode;
    }

    public function getCurrentCurrencyOfDomain(int $domainId): Currency
    {
        $domainConfig = $this->domain->getDomainConfigById($domainId);

        if ($this->currentCurrencyCode !== null && $domainConfig->hasCurrencyCode($this->currentCurrencyCode)) {
            return $this->currencyFacade->getByCode($this->currentCurrencyCode);
        }

        return $this->currencyFacade->getByCode($domainConfig->getDefaultCurrencyCode());
    }

    public function getCurrentCurrencyOfCurrentDomain(): Currency
    {
        return $this->getCurrentCurrencyOfDomain($this->domain->getId());
    }

    public function reset(): void
    {
        $this->currentCurrencyCode = null;
    }
}
