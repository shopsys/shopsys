<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

class CartMigrationFacade
{
    protected const SESSION_PREVIOUS_CART_IDENTIFIER = 'previous_id';

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        protected readonly CartItemFactoryInterface $cartItemFactory,
        protected readonly CartFacade $cartFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    public function mergeCurrentCartWithCart(Cart $cart): void
    {
        $customerUserIdentifier = $this->customerUserIdentifierFactory->get();
        $currentCart = $this->cartFacade->getCartByCustomerUserIdentifierCreateIfNotExists($customerUserIdentifier);

        foreach ($cart->getItems() as $itemToMerge) {
            $similarItem = $currentCart->findSimilarItemByItem($itemToMerge);

            if ($similarItem instanceof CartItem) {
                $similarItem->changeQuantity($similarItem->getQuantity() + $itemToMerge->getQuantity());
            } else {
                $newCartItem = $this->cartItemFactory->create(
                    $currentCart,
                    $itemToMerge->getProduct(),
                    $itemToMerge->getQuantity(),
                    $itemToMerge->getWatchedPrice(),
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
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $session = $event->getRequest()->getSession();

        $previousCartIdentifier = $session->get(static::SESSION_PREVIOUS_CART_IDENTIFIER);

        if (
            $previousCartIdentifier !== null
            && $previousCartIdentifier !== ''
            && $previousCartIdentifier !== $session->getId()
        ) {
            $previousCustomerUserIdentifier = $this->customerUserIdentifierFactory->getOnlyWithCartIdentifier(
                $previousCartIdentifier,
            );
            $cart = $this->cartFacade->findCartByCustomerUserIdentifier($previousCustomerUserIdentifier);

            if ($cart !== null) {
                $this->mergeCurrentCartWithCart($cart);
            }
        }
        $session->set(static::SESSION_PREVIOUS_CART_IDENTIFIER, $session->getId());
    }
}
