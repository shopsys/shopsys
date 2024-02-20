<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Resolver\Settings;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrontendApiBundle\Model\Resolver\AbstractQuery;

class PricingSettingsQuery extends AbstractQuery
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly CurrencyFacade $currencyFacade,
    ) {
    }

    /**
     * @return array{defaultCurrencyCode: string, minimumFractionDigits: int}
     */
    public function pricingSettingsQuery(): array
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return [
            'defaultCurrencyCode' => $currency->getCode(),
            'minimumFractionDigits' => $currency->getMinFractionDigits(),
        ];
    }
}
