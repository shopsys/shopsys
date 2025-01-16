<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

interface ProductPriceInterface
{
    /**
     * @return bool
     */
    public function isPriceFrom(): bool;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup $pricingGroup
     * @return self
     */
    public static function createHiddenProductPrice(PricingGroup $pricingGroup): self;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\PriceInterface
     */
    public function getPrice(): PriceInterface;

    /**
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup
     */
    public function getPricingGroup(): PricingGroup;
}
