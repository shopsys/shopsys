<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;

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
 * @method \App\Model\Cart\Item\CartItem getItemByUuid(string $itemUuid)
 * @method assignCartToCustomerUser(\App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Product\Product[] getProducts()
 */
class Cart extends BaseCart
{
}
