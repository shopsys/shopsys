<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;

/**
 * @property \App\Model\Customer\User\CustomerUser|null $customerUser
 * @property \Doctrine\Common\Collections\Collection<int,\App\Model\Cart\Item\CartItem> $items
 * @method void addItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Cart\Item\CartItem[] getItems()
 * @method \App\Model\Cart\Item\CartItem getItemById(int $itemId)
 * @method \App\Model\Cart\Item\CartItem|null findSimilarItemByItem(\App\Model\Cart\Item\CartItem $item)
 * @method \App\Model\Customer\User\CustomerUser|null getCustomerUser()
 * @property \App\Model\Transport\Transport|null $transport
 * @property \App\Model\Payment\Payment|null $payment
 * @method __construct(string $cartIdentifier, \App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Transport\Transport|null getTransport()
 * @method \App\Model\Payment\Payment|null getPayment()
 * @method \App\Model\Cart\Item\CartItem getItemByUuid(string $itemUuid)
 * @method void assignCartToCustomerUser(\App\Model\Customer\User\CustomerUser|null $customerUser)
 * @method \App\Model\Product\Product[] getProducts()
 * @method \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct createQuantifiedProduct(\App\Model\Cart\Item\CartItem $cartItem)
 * @method \App\Model\Cart\Item\CartItem[] getProductGiftCartItems()
 * @method \App\Model\Cart\Item\CartItem[] getProductCartItems()
 */
#[ORM\Table(name: 'carts')]
#[ORM\Entity]
class Cart extends BaseCart
{
}
