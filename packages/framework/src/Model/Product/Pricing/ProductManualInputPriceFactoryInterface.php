<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Product;

interface ProductManualInputPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $inputPrice
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPrice
     */
    public function create(
        Product $product,
        PricingGroup $pricingGroup,
        ?Money $inputPrice,
    ): ProductManualInputPrice;
}
