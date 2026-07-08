<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;

class PriceFacade
{
    public function __construct(
        protected readonly PriceFactory $priceFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly Domain $domain,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
    ) {
    }

    public function createProductPriceFromArrayForCurrentCustomer(array $pricesArray): ProductPriceInterface
    {
        return $this->priceFactory->createProductPriceFromArrayByPricingGroup(
            $pricesArray,
            $this->currentCustomerUser->getPricingGroup(),
            $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($this->domain->getId())->getCode(),
        );
    }
}
