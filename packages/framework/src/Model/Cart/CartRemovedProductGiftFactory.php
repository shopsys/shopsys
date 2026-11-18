<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Product;

class CartRemovedProductGiftFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(Cart $cart, Product $mainProduct, Product $giftProduct): CartRemovedProductGift
    {
        $entityClassName = $this->entityNameResolver->resolve(CartRemovedProductGift::class);

        return new $entityClassName($cart, $mainProduct, $giftProduct);
    }
}
