<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class AddProductResult
{
    public function __construct(
        protected readonly CartItem $cartItem,
        protected readonly bool $isNew,
        protected readonly int $addedQuantity,
        protected readonly int $notOnStockQuantity,
    ) {
    }

    public function getCartItem(): CartItem
    {
        return $this->cartItem;
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    public function getAddedQuantity(): int
    {
        return $this->addedQuantity;
    }

    public function getNotOnStockQuantity(): int
    {
        return $this->notOnStockQuantity;
    }
}
