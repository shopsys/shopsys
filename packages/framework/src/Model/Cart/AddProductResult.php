<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class AddProductResult
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    private $cartItem;

    /**
     * @var bool
     */
    private $isNew;

    /**
     * @var int
     */
    private $addedQuantity;

    public function __construct(CartItem $cartItem, bool $isNew, int $addedQuantity)
    {
        $this->cartItem = $cartItem;
        $this->isNew = $isNew;
        $this->addedQuantity = $addedQuantity;
    }

    public function getCartItem(): \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
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
}
