<?php

namespace Shopsys\FrameworkBundle\Model\Order\Item;

use Shopsys\FrameworkBundle\Model\Product\Product;

class QuantifiedProduct
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Product
     */
    private $product;

    /**
     * @var int
     */
    private $quantity;
    
    public function __construct(Product $product, int $quantity)
    {
        $this->product = $product;
        $this->quantity = $quantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\Product $product
     */
    public function getProduct(): \Shopsys\FrameworkBundle\Model\Product\Product
    {
        return $this->product;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
