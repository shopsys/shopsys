<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class AddProductResult
{
    protected bool $isNew;

    protected int $addedQuantity;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem $cartItem
     * @param bool $isNew
     * @param int $addedQuantity
     */
    public function __construct(protected readonly CartItem $cartItem, bool $isNew, int $addedQuantity)
    {
        $this->isNew = $isNew;
        $this->addedQuantity = $addedQuantity;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    public function getCartItem(): \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
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
}
