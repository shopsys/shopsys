<?php

namespace Shopsys\FrameworkBundle\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

class Cart
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    private $cartItems;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] $cartItems
     */
    public function __construct(array $cartItems)
    {
        $this->cartItems = $cartItems;
    }

    public function addItem(CartItem $item): void
    {
        $this->cartItems[] = $item;
    }

    public function removeItemById(int $cartItemId): void
    {
        foreach ($this->cartItems as $key => $cartItem) {
            if ($cartItem->getId() === $cartItemId) {
                unset($this->cartItems[$key]);
                return;
            }
        }
        $message = 'Cart item with ID = ' . $cartItemId . ' is not in cart for remove.';
        throw new \Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException($message);
    }

    public function clean(): void
    {
        $this->cartItems = [];
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getItems(): array
    {
        return $this->cartItems;
    }

    public function getItemsCount(): int
    {
        return count($this->getItems());
    }

    public function isEmpty(): bool
    {
        return $this->getItemsCount() === 0;
    }
}
