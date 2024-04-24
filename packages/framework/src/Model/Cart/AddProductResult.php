<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class AddProductResult
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @param bool $isNew
     * @param int $addedQuantity
     * @param int $notOnStockQuantity
     */
    public function __construct(
        protected readonly CartItem $cartItem,
        protected readonly bool $isNew,
        protected readonly int $addedQuantity,
        protected readonly int $notOnStockQuantity,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    /**
     * @return bool
     */
    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    /**
     * @return int
     */
    public function getAddedQuantity(): int
    {
        return $this->addedQuantity;
    }

    /**
     * @return int
     */
    public function getNotOnStockQuantity(): int
    {
        return $this->notOnStockQuantity;
    }
}
