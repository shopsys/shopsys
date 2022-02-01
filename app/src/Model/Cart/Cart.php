<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Cart\Item\CartItem;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \App\Model\Cart\Item\CartItem[]|\Doctrine\Common\Collections\Collection $items
 * @method __construct(string $cartIdentifier, \App\Model\Customer\User\CustomerUser|null $customerUser = null)
 * @method addItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Cart\Item\CartItem[] getItems()
 * @method \App\Model\Cart\Item\CartItem getItemById(int $itemId)
 * @method \App\Model\Cart\Item\CartItem|null findSimilarItemByItem(\App\Model\Cart\Item\CartItem $item)
 */
class Cart extends BaseCart
{
    /**
     * @inheritDoc
     */
    public function getQuantifiedProducts(): array
    {
        $quantifiedProducts = [];
        foreach ($this->items as $item) {
            $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
        }

        return $quantifiedProducts;
    }

    /**
     * @return int
     */
    public function getTotalWeight(): int
    {
        $totalWeight = 0;
        foreach ($this->items as $item) {
            $product = $item->getProduct();
            $totalWeight += $product->getWeight() * $item->getQuantity();
        }

        return $totalWeight;
    }

    /**
     * @param string $itemUuid
     * @return \App\Model\Cart\Item\CartItem
     */
    public function getItemByUuid(string $itemUuid): CartItem
    {
        foreach ($this->items as $item) {
            if ($item->getUuid() === $itemUuid) {
                return $item;
            }
        }

        $message = 'Cart item with UUID "' . $itemUuid . '" not found in cart.';
        throw new InvalidCartItemException($message);
    }
}
