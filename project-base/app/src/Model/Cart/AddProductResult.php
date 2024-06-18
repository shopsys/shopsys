<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult as BaseAddProductResult;

/**
 * @property \App\Model\Cart\Item\CartItem $cartItem
 * @method \App\Model\Cart\Item\CartItem getCartItem()
 * @method __construct(\App\Model\Cart\Item\CartItem $cartItem, bool $isNew, int $addedQuantity, int $notOnStockQuantity)
 */
class AddProductResult extends BaseAddProductResult
{
}
