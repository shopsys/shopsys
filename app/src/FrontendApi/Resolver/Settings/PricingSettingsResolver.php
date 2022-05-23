<?php

declare(strict_types=1);

namespace App\FrontendApi\Resolver\Settings;

use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;

class PricingSettingsResolver implements ResolverInterface, AliasedInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected Domain $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected CurrencyFacade $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(Domain $domain, CurrencyFacade $currencyFacade)
    {
        $this->domain = $domain;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @return array{defaultCurrencyCode: string, minimumFractionDigits: int}
     */
    public function resolve(): array
    {
        $currency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($this->domain->getId());

        return [
            'defaultCurrencyCode' => $currency->getCode(),
            'minimumFractionDigits' => $currency->getMinFractionDigits(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function getAliases(): array
    {
        return ['resolve' => 'pricingSettings'];
    }
}
