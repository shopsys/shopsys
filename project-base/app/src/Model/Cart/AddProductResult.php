<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\AddProductResult as BaseAddProductResult;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

/**
 * @property \App\Model\Cart\Item\CartItem $cartItem
 * @method \App\Model\Cart\Item\CartItem getCartItem()
 */
class AddProductResult extends BaseAddProductResult
{
    protected int $notOnStockQuantity;

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     * @param mixed $isNew
     * @param mixed $addedQuantity
     * @param mixed $notOnStockQuantity
     */
    public function __construct(
        CartItem $cartItem,
        bool $isNew,
        int $addedQuantity,
        int $notOnStockQuantity,
    ) {
        parent::__construct($cartItem, $isNew, $addedQuantity);

        $this->notOnStockQuantity = $notOnStockQuantity;
    }

    /**
     * @return int
     */
    public function getNotOnStockQuantity(): int
    {
        return $this->notOnStockQuantity;
    }
}
