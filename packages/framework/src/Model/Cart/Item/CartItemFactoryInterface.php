<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Product;

interface CartItemFactoryInterface
{

    public function create(
        CustomerIdentifier $customerIdentifier,
        Product $product,
        int $quantity,
        string $watchedPrice
    ): CartItem;
}
