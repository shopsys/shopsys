<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Shopsys\FrameworkBundle\Model\Cart\Cart as BaseCart;
use Shopsys\FrameworkBundle\Model\Cart\CartMigrationFacade as BaseCartMigrationFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;

/**
 * @property \App\Model\Cart\CartFacade $cartFacade
 * @property \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
 * @method __construct(\Doctrine\ORM\EntityManagerInterface $em, \App\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory, \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory, \App\Model\Cart\CartFacade $cartFacade)
 */
class CartMigrationFacade extends BaseCartMigrationFacade
{
    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function mergeCurrentCartWithCart(BaseCart $cart): void
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();
        $currentCart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $this->mergeCarts($cart, $currentCart);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Cart\Cart $currentCart
     */
    public function mergeCarts(Cart $cart, Cart $currentCart): void
    {
        foreach ($cart->getItems() as $itemToMerge) {
            $similarItem = $currentCart->findSimilarItemByItem($itemToMerge);
            if ($similarItem instanceof CartItem) {
                $similarItem->changeQuantity($similarItem->getQuantity() + $itemToMerge->getQuantity());
            } else {
                /** @var \App\Model\Cart\Item\CartItem $newCartItem */
                $newCartItem = $this->cartItemFactory->create(
                    $currentCart,
                    $itemToMerge->getProduct(),
                    $itemToMerge->getQuantity(),
                    $itemToMerge->getWatchedPrice()
                );
                $currentCart->addItem($newCartItem);
                $this->em->persist($newCartItem);
            }
        }
        $currentCart->setModifiedNow();

        $this->em->flush();

        $this->cartFacade->deleteCart($cart);
    }
}
