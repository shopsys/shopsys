<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Feed;

use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\Feed\Exception\FeedCurrencyNotEnabledOnDomainException;

/**
 * Resolves the currencies a feed is generated in on the given domain:
 * - no configuration (default) generates only in the domain default currency
 * - 'all' generates in every currency enabled on the domain
 * - 'EUR,CZK' generates in the intersection of the list and the domain enabled currencies
 * - {1: [EUR, CZK]} generates the listed currencies per domain (unknown code fails fast), unlisted domains use the default only
 */
class FeedCurrencyResolver
{
    /**
     * @return string[]
     */
    public function resolveCurrencyCodes(FeedConfig $feedConfig, DomainConfig $domainConfig): array
    {
        $currenciesConfig = $feedConfig->getCurrenciesConfig();
        $domainCurrencyCodes = $domainConfig->getCurrencyCodes();
        $defaultCurrencyCode = $domainConfig->getDefaultCurrencyCode();

        if ($currenciesConfig === null) {
            return [$defaultCurrencyCode];
        }

        if ($currenciesConfig === 'all') {
            return $this->sortDefaultCurrencyCodeFirst($domainCurrencyCodes, $defaultCurrencyCode);
        }

        if (is_string($currenciesConfig)) {
            $requestedCurrencyCodes = array_map('trim', explode(',', $currenciesConfig));

            return $this->sortDefaultCurrencyCodeFirst(
                array_values(array_intersect($requestedCurrencyCodes, $domainCurrencyCodes)),
                $defaultCurrencyCode,
            );
        }

        $requestedCurrencyCodes = $currenciesConfig[$domainConfig->getId()] ?? [$defaultCurrencyCode];

        foreach ($requestedCurrencyCodes as $currencyCode) {
            if (!$domainConfig->hasCurrencyCode($currencyCode)) {
                throw new FeedCurrencyNotEnabledOnDomainException(
                    $feedConfig->getFeed()->getInfo()->getName(),
                    $currencyCode,
                    $domainConfig->getId(),
                );
            }
        }

        return $this->sortDefaultCurrencyCodeFirst(array_values(array_unique($requestedCurrencyCodes)), $defaultCurrencyCode);
    }

    /**
     * @param string[] $currencyCodes
     * @return string[]
     */
    protected function sortDefaultCurrencyCodeFirst(array $currencyCodes, string $defaultCurrencyCode): array
    {
        usort(
            $currencyCodes,
            static fn (string $currencyCodeA, string $currencyCodeB) => (int)($currencyCodeA !== $defaultCurrencyCode) <=> (int)($currencyCodeB !== $defaultCurrencyCode),
        );

        return $currencyCodes;
    }
}
