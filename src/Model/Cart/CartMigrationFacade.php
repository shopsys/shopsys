<?php

declare(strict_types=1);

namespace App\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Cart\CartMigrationFacade as BaseCartMigrationFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;

/**
 * @property \App\Model\Cart\CartFacade $cartFacade
 */
class CartMigrationFacade extends BaseCartMigrationFacade
{
    /**
     * @var bool
     */
    private bool $cartChanged;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \App\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(EntityManagerInterface $em, CustomerUserIdentifierFactory $customerUserIdentifierFactory, CartItemFactoryInterface $cartItemFactory, CartFacade $cartFacade)
    {
        parent::__construct($em, $customerUserIdentifierFactory, $cartItemFactory, $cartFacade);

        $this->cartChanged = false;
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    public function mergeCurrentCartWithCart(Cart $cart): void
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();
        $currentCart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        $this->cartChanged = false;
        if ($cart->isEmpty() === false && $currentCart->isEmpty() === false) {
            $this->cartChanged = true;
        }

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
            }
        }
        $currentCart->setModifiedNow();

        foreach ($currentCart->getItems() as $item) {
            $this->em->persist($item);
        }

        $this->cartFacade->deleteCart($cart);

        $this->em->flush();
    }

    /**
     * @return bool
     */
    public function isCartChanged(): bool
    {
        return $this->cartChanged;
    }
}
