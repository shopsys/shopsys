<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\CartFacade as BaseCartFacade;

/**
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \Shopsys\FrameworkBundle\Model\Cart\CartFactory $cartFactory, \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory, \Shopsys\FrameworkBundle\Component\Domain\Domain $domain, \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculation, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory $cartItemFactory, \Shopsys\FrameworkBundle\Model\Cart\CartRepository $cartRepository, \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade $giftCartFacade, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation $additionalServicePriceCalculation)
 * @method \App\Model\Cart\AddProductResult addProductToExistingCart(\App\Model\Product\Product $product, int $quantity, \App\Model\Cart\Cart $cart, bool $isAbsoluteQuantity = false)
 * @method void deleteCart(\App\Model\Cart\Cart $cart)
 * @method \App\Model\Cart\Cart|null findCartByCustomerUserIdentifier(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @method \App\Model\Cart\Cart getCartByCustomerUserIdentifierCreateIfNotExists(\Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier $customerUserIdentifier)
 * @method \App\Model\Cart\Cart|null findCartByCartIdentifier(string $cartIdentifier)
 * @method \App\Model\Cart\Cart removeItemFromExistingCartByUuid(string $cartItemUuid, \App\Model\Cart\Cart $cart)
 * @method \App\Model\Cart\Cart setItemAdditionalServicesInExistingCartByUuid(string $cartItemUuid, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $additionalServices, \App\Model\Cart\Cart $cart)
 * @method array<int, string> calculateAdditionalServicesWatchedPrices(\App\Model\Cart\Item\CartItem $cartItem)
 */
class CartFacade extends BaseCartFacade
{
}
