<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

class AddOrderItemsToCartResult
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $notAddedProducts
     */
    public function __construct(
        protected readonly array $notAddedProducts,
        protected readonly bool $someProductWasRemovedFromEshop,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    public function getNotAddedProducts(): array
    {
        return $this->notAddedProducts;
    }

    public function isSomeProductRemovedFromEshop(): bool
    {
        return $this->someProductWasRemovedFromEshop;
    }
}
