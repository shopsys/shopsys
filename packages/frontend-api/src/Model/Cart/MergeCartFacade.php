<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;

class MergeCartFacade
{
    protected bool $showCartMergeInfo = false;

    public function __construct(
        protected readonly CartApiFacade $cartFacade,
        protected readonly CartItemFactory $cartItemFactory,
        protected readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function mergeCartByUuidToCustomerCart(string $cartUuid, CustomerUser $customerUser): void
    {
        $oldCart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

        if (!$oldCart->isEmpty() && !$customerCart->isEmpty()) {
            $this->showCartMergeInfo = true;
        }

        $this->mergeCarts($oldCart, $customerCart);
    }

    public function overwriteCustomerCartWithCartByUuid(string $cartUuid, CustomerUser $customerUser): void
    {
        $oldCart = $this->cartFacade->getCartCreateIfNotExists(null, $cartUuid);
        $customerCart = $this->cartFacade->getCartCreateIfNotExists($customerUser, null);

        $this->cartFacade->deleteCart($customerCart);

        $oldCart->assignCartToCustomerUser($customerUser);

        $this->entityManager->flush();
    }

    protected function mergeCarts(Cart $cart, Cart $currentCart): void
    {
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
                    $itemToMerge->getType(),
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

    public function shouldShowCartMergeInfo(): bool
    {
        return $this->showCartMergeInfo;
    }
}
