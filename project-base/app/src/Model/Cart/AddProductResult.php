<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult as BaseAddProductResult;

/**
 * @property \App\Model\Order\Item\OrderItem $cartItem
 * @method \App\Model\Order\Item\OrderItem getCartItem()
 * @method __construct(\App\Model\Order\Item\OrderItem $cartItem, bool $isNew, int $addedQuantity, int $notOnStockQuantity)
 */
class AddProductResult extends BaseAddProductResult
{
}
