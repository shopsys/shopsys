<?php

declare(strict_types=1);

namespace App\Model\Cart;

use App\Model\Cart\Item\CartItem;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;

/**
 * @ORM\Table(name="carts")
 * @ORM\Entity
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Cart\Item\CartItem> $items
 * @method addItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Cart\Item\CartItem[] getItems()
 * @method \App\Model\Cart\Item\CartItem getItemById(int $itemId)
 * @method \App\Model\Cart\Item\CartItem|null findSimilarItemByItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUser()
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Order\PromoCode\PromoCode> $promoCodes
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @method __construct(string $cartIdentifier, \App\Model\Customer\User\CustomerUser|null $customerUser = null)
 * @method \App\Model\Order\PromoCode\PromoCode[] getAllAppliedPromoCodes()
 * @method \App\Model\Order\PromoCode\PromoCode|null getFirstAppliedPromoCode()
 * @method applyPromoCode(\App\Model\Order\PromoCode\PromoCode $promoCode)
 * @method \App\Model\Transport\Transport|null getTransport()
 * @method \App\Model\Payment\Payment|null getPayment()
 */
class Cart extends BaseCart
{
    /**
     * {@inheritdoc}
     */
    public function getQuantifiedProducts(): array
    {
        $quantifiedProducts = [];

        foreach ($this->items as $item) {
            try {
                $quantifiedProducts[$item->getId()] = new QuantifiedProduct($item->getProduct(), $item->getQuantity());
            } catch (ProductNotFoundException $productNotFoundException) {
                continue;
            }
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
            try {
                $product = $item->getProduct();
                $totalWeight += $product->getWeight() * $item->getQuantity();
            } catch (ProductNotFoundException $productNotFoundException) {
                continue;
            }
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

    /**
     * @param \App\Model\Customer\User\CustomerUser|null $customerUser
     */
    public function assignCartToCustomerUser(?CustomerUser $customerUser): void
    {
        $this->customerUser = $customerUser;
        $this->cartIdentifier = '';
        $this->setModifiedNow();
    }
}
