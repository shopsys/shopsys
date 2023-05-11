<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Price;

use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;

class PriceFacade
{
    protected PriceFactory $priceFactory;

    protected CurrentCustomerUser $currentCustomerUser;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\PriceFactory $priceFactory
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     */
    public function __construct(
        PriceFactory $priceFactory,
        CurrentCustomerUser $currentCustomerUser
    ) {
        $this->priceFactory = $priceFactory;
        $this->currentCustomerUser = $currentCustomerUser;
    }

    /**
     * @param array $pricesArray
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice
     */
    public function createProductPriceFromArrayForCurrentCustomer(array $pricesArray): ProductPrice
    {
        return $this->priceFactory->createProductPriceFromArrayByPricingGroup(
            $pricesArray,
            $this->currentCustomerUser->getPricingGroup()
        );
    }
}
