<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

class PriceWithCurrencyFactory
{
    public function __construct(protected readonly CurrencyFacade $currencyFacade)
    {
    }

    /**
     * The lookup by code is safe as the currencies used in orders can not be deleted
     */
    public function createWithCurrencyCode(PriceInterface $price, string $currencyCode): Price
    {
        return new Price(
            $price->getPriceWithoutVat(),
            $price->getPriceWithVat(),
            $this->currencyFacade->getByCode($currencyCode),
        );
    }
}
