<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceInterface;

class PriceFacade
{
    public function __construct(
        protected readonly PriceFactory $priceFactory,
        protected readonly CurrentCustomerUser $currentCustomerUser,
    ) {
    }

    public function createProductPriceFromArrayForCurrentCustomer(array $pricesArray): ProductPriceInterface
    {
        return $this->priceFactory->createProductPriceFromArrayByPricingGroup(
            $pricesArray,
            $this->currentCustomerUser->getPricingGroup(),
        );
    }
}
