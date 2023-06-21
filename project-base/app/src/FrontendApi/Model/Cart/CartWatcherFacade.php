<?php

declare(strict_types=1);

namespace App\FrontendApi\Model\Cart;

use App\Model\Cart\Cart;
use App\Model\Cart\CartPromoCodeFacade;
use App\Model\Order\PromoCode\CurrentPromoCodeFacade;
use App\Model\Product\Availability\ProductAvailabilityFacade;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;

class CartWatcherFacade
{
    private CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \App\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\FrontendApi\Model\Cart\TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade
     * @param \App\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \App\Model\Cart\CartPromoCodeFacade $cartPromoCodeFacade
     */
    public function __construct(
        private CartWatcher $cartWatcher,
        private EntityManagerInterface $em,
        private CurrentCustomerUser $currentCustomerUser,
        private ProductAvailabilityFacade $productAvailabilityFacade,
        private Domain $domain,
        private TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        private CurrentPromoCodeFacade $currentPromoCodeFacade,
        private CartPromoCodeFacade $cartPromoCodeFacade,
    ) {
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     * @return \App\FrontendApi\Model\Cart\CartWithModificationsResult
     */
    public function getCheckedCartWithModifications(Cart $cart): CartWithModificationsResult
    {
        $this->cartWithModificationsResult = new CartWithModificationsResult($cart);

        $this->checkRemovedProductsItems($cart);
        $this->checkNotListableItems($cart);
        $this->checkUnavailableStockQuantityItems($cart);
        $this->checkModifiedPrices($cart);
        $this->checkPromoCodeValidity($cart);

        $this->em->flush();

        return $this->transportAndPaymentWatcherFacade->checkTransportAndPayment($this->cartWithModificationsResult, $cart);
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkRemovedProductsItems(Cart $cart): void
    {
        foreach ($cart->getItems() as $cartItem) {
            if (!$cartItem->hasProduct()) {
                $cart->removeItemById($cartItem->getId());
                $this->em->remove($cartItem);

                $this->cartWithModificationsResult->setCartHasRemovedProducts();
            }
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkModifiedPrices(Cart $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        /** @var \App\Model\Cart\Item\CartItem $cartItem */
        foreach ($modifiedItems as $cartItem) {
            $this->cartWithModificationsResult->addCartItemWithModifiedPrice($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkNotListableItems(Cart $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        /** @var \App\Model\Cart\Item\CartItem $cartItem */
        foreach ($notVisibleItems as $cartItem) {
            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);

            $this->cartWithModificationsResult->addNoLongerListableCartItem($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkUnavailableStockQuantityItems(Cart $cart): void
    {
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();
            $maximumOrderQuantity = $this->productAvailabilityFacade->getMaximumOrderQuantity($product, $this->domain->getId());

            if ($maximumOrderQuantity === 0) {
                $cart->removeItemById($cartItem->getId());
                $this->cartWithModificationsResult->addNoLongerAvailableCartItemDueToQuantity($cartItem);

                continue;
            }

            if ($cartItem->getQuantity() <= $maximumOrderQuantity) {
                continue;
            }

            $cartItem->changeQuantity($maximumOrderQuantity);
            $cartItem->changeAddedAt(new DateTime());
            $this->em->persist($cartItem);

            $this->cartWithModificationsResult->addCartItemWithChangedQuantity($cartItem);
        }
    }

    /**
     * @param \App\Model\Cart\Cart $cart
     */
    private function checkPromoCodeValidity(Cart $cart): void
    {
        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            try {
                $this->currentPromoCodeFacade->getValidatedPromoCode($promoCode->getCode(), $cart);
            } catch (PromoCodeException $exception) {
                $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
                $this->cartWithModificationsResult->addChangedPromoCode($promoCode->getCode());
            }
        }
    }
}
