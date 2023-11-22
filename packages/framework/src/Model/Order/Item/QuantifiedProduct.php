<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProduct
{
    protected int $quantity;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     */
    public function __construct(protected readonly Product $product, int $quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product
     */
    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
