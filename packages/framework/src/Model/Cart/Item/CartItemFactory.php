<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CartItemFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly CartItemTypeEnum $cartItemTypeEnum,
    ) {
    }

    public function create(
        Cart $cart,
        Product $product,
        int $quantity,
        ?Money $watchedPrice,
        string $type,
    ): CartItem {
        $this->cartItemTypeEnum->validateCase($type);
        $entityClassName = $this->entityNameResolver->resolve(CartItem::class);

        return new $entityClassName($cart, $product, $quantity, $watchedPrice, $type);
    }
}
