<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\Cart;
use App\Model\Customer\User\CustomerUser;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;

class MergeCartFacade
{
    private bool $showCartMergeInfo = false;

    /**
     * @param \App\FrontendApi\Model\Cart\CartFacade $cartFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory $cartItemFactory
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     */
    public function __construct(
        private CartFacade $cartFacade,
        private CartItemFactory $cartItemFactory,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function mergeCartByUuidToCustomerCart(string $cartUuid, CustomerUser $customerUser): void
    {
        $oldCart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

        if (!$oldCart->isEmpty() && !$customerCart->isEmpty()) {
            $this->showCartMergeInfo = true;
        }

        $this->mergeCarts($oldCart, $customerCart);
    }

    /**
     * @param string $cartUuid
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     */
    public function overwriteCustomerCartWithCartByUuid(string $cartUuid, CustomerUser $customerUser): void
    {
        $oldCart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

        $this->cartFacade->deleteCart($customerCart);

        $oldCart->assignCartToCustomerUser($customerUser);

        $this->entityManager->flush();
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @param \App\Model\Cart\Cart $currentCart
     */
    private function mergeCarts(Cart $cart, Cart $currentCart): void
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
                    $itemToMerge->getWatchedPrice(),
                );
                $currentCart->addItem($newCartItem);
                $this->entityManager->persist($newCartItem);
            }
        }

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            $currentCart->applyPromoCode($promoCode);
        }

        $currentCart->setModifiedNow();

        $this->entityManager->flush();

        $this->cartFacade->deleteCart($cart);
    }

    /**
     * @return bool
     */
    public function shouldShowCartMergeInfo(): bool
    {
        return $this->showCartMergeInfo;
    }
}
