<?php

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
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $em;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory
     */
    protected $customerUserIdentifierFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     */
    protected $cartItemFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    protected $cartFacade;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifierFactory $customerUserIdentifierFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface $cartItemFactory
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartFacade $cartFacade
     */
    public function __construct(
        EntityManagerInterface $em,
        CustomerUserIdentifierFactory $customerUserIdentifierFactory,
        CartItemFactoryInterface $cartItemFactory,
        CartFacade $cartFacade
    ) {
        $this->em = $em;
        $this->customerUserIdentifierFactory = $customerUserIdentifierFactory;
        $this->cartItemFactory = $cartItemFactory;
        $this->cartFacade = $cartFacade;
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
                $previousCartIdentifier
            );
            $cart = $this->cartFacade->findCartByCustomerUserIdentifier($previousCustomerUserIdentifier);

            if ($cart !== null) {
                $this->mergeCurrentCartWithCart($cart);
            }
        }
        $session->set(static::SESSION_PREVIOUS_CART_IDENTIFIER, $session->getId());
    }
}
