<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\CartFacade as BaseCartFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @property \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
 * @property \App\Model\Order\OrderDataFactory $orderDataFactory
 * @property \App\Model\Order\Item\OrderItemFactory $orderItemFactory
 * @property \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory $cartItemFactory, \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade, \App\Model\Order\OrderDataFactory $orderDataFactory, \App\Model\Order\Item\OrderItemFactory $orderItemFactory, \App\Model\Order\Item\OrderItemDataFactory $orderItemDataFactory)
 * @method \App\Model\Cart\AddProductResult addProductToExistingCart(\App\Model\Product\Product $product, int $quantity, \App\Model\Order\Order $cart, bool $isAbsoluteQuantity = false)
 * @method deleteCart(\App\Model\Cart\Cart $cart)
 * @method \App\Model\Product\Product getProductByCartItemId(int $cartItemId)
 * @method \App\Model\Order\Order|null findCartByCustomerUserIdentifier(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @method \App\Model\Order\Order|null findCartOfCurrentCustomerUser()
 * @method \App\Model\Order\Order getCartByCustomerUserIdentifierCreateIfNotExists(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @method \App\Model\Order\Order|null findCartByCartIdentifier(string $cartIdentifier)
 * @method \App\Model\Cart\Cart removeItemFromExistingCartByUuid(string $cartItemUuid, \App\Model\Cart\Cart $cart)
 * @method \App\Model\Order\Item\OrderItem createNewCartItem(\App\Model\Product\Product $product, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice $productPrice, int $quantity, \App\Model\Order\Order $cart)
 */
class CartFacade extends BaseCartFacade
{
}
