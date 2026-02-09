<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Pricing;

use Override;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;

final class ProductPrice implements ProductPriceInterface
{
    public function __construct(
        private readonly PriceInterface $price,
        private readonly PricingGroup $pricingGroup,
        private readonly bool $priceFrom = false,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isPriceFrom(): bool
    {
        return $this->priceFrom;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function createHiddenProductPrice(PricingGroup $pricingGroup): static
    {
        return new self(
            Price::createHiddenPrice(),
            $pricingGroup,
            false,
        );
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPrice(): PriceInterface
    {
        return $this->price;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getPricingGroup(): PricingGroup
    {
        return $this->pricingGroup;
    }
}
