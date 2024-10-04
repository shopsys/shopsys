<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\CartFacade as BaseCartFacade;

/**
 * @property \App\Model\Product\ProductRepository $productRepository
 * @method \App\Model\Product\Product getProductByCartItemId(int $cartItemId)
 * @method \App\Model\Cart\Cart|null findCartOfCurrentCustomerUser()
 * @method \App\Model\Cart\Cart getCartByCustomerUserIdentifierCreateIfNotExists(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @property \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
 * @property \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
 * @method \App\Model\Cart\Cart|null findCartByCartIdentifier(string $cartIdentifier)
 * @method deleteCart(\App\Model\Cart\Cart $cart)
 * @method \App\Model\Cart\Cart|null findCartByCustomerUserIdentifier(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @method \App\Model\Cart\AddProductResult addProductToExistingCart(\App\Model\Product\Product $product, int $quantity, \App\Model\Cart\Cart $cart, bool $isAbsoluteQuantity = false)
 * @method \App\Model\Cart\Cart removeItemFromExistingCartByUuid(string $cartItemUuid, \App\Model\Cart\Cart $cart)
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory, \App\Model\Product\ProductRepository $productRepository, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser, \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory, \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade)
 */
class CartFacade extends BaseCartFacade
{
}
