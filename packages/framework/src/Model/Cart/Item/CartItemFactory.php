<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Item;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CartItemFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum $cartItemTypeEnum
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
        protected readonly CartItemTypeEnum $cartItemTypeEnum,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $quantity
     * @param \Shopsys\FrameworkBundle\Component\Money\Money|null $watchedPrice
     * @param string $type
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
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
