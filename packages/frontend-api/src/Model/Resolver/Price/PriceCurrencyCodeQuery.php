<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrontendApiBundle\Model\Price\PriceInfo;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PriceCurrencyCodeQuery extends AbstractQuery
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    /**
     * Prices carrying their own currency (e.g. order price snapshots) win over the currently selected currency
     */
    public function currencyCodeByPriceQuery(PriceInterface|PriceInfo $price): string
    {
        if ($price instanceof PriceInfo && $price->currencyCode !== null) {
            return $price->currencyCode;
        }

        if ($price instanceof PriceInterface && $price->getCurrency() !== null) {
            return $price->getCurrency()->getCode();
        }

        return $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($this->domain->getId())->getCode();
    }
}
