<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult as BaseAddProductResult;

/**
 * @property \App\Model\Cart\Item\CartItem $cartItem
 * @method \App\Model\Cart\Item\CartItem getCartItem()
 */
class AddProductResult extends BaseAddProductResult
{
}
