<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\Cart;
use App\Model\Cart\Item\CartItem;

class CartWithModificationsResult
{
    /**
     * @var \App\Model\Cart\Cart
     */
    protected Cart $cart;

    /**
     * @var array<string, array<int, \App\Model\Cart\Item\CartItem>>
     */
    private array $cartModifications = [
        'noLongerListableCartItems' => [],
        'cartItemsWithModifiedPrice' => [],
        'cartItemsWithChangedQuantity' => [],
        'noLongerAvailableCartItemsDueToQuantity' => [],
    ];

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->cart->getCartIdentifier();
    }

    /**
     * @return \App\Model\Cart\Item\CartItem[]
     */
    public function getItems(): array
    {
        return $this->cart->getItems();
    }

    /**
     * @return array<string, array<int, \App\Model\Cart\Item\CartItem>>
     */
    public function getModifications(): array
    {
        return $this->cartModifications;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerListableCartItem(CartItem $cartItem): void
    {
        $this->cartModifications['noLongerListableCartItems'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithModifiedPrice(CartItem $cartItem): void
    {
        $this->cartModifications['cartItemsWithModifiedPrice'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addCartItemWithChangedQuantity(CartItem $cartItem): void
    {
        $this->cartModifications['cartItemsWithChangedQuantity'][] = $cartItem;
    }

    /**
     * @param \App\Model\Cart\Item\CartItem $cartItem
     */
    public function addNoLongerAvailableCartItemDueToQuantity(CartItem $cartItem): void
    {
        $this->cartModifications['noLongerAvailableCartItemsDueToQuantity'][] = $cartItem;
    }
}
