<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

interface ProductPriceInterface
{
    public function isPriceFrom(): bool;

    public static function createHiddenProductPrice(PricingGroup $pricingGroup): static;

    public function getPrice(): PriceInterface;

    public function getPricingGroup(): PricingGroup;
}
