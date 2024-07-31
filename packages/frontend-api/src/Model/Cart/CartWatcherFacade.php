<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;

class CartWatcherFacade
{
    protected CartWithModificationsResult $cartWithModificationsResult;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher $cartWatcher
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade $productAvailabilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrontendApiBundle\Model\Cart\TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade $currentPromoCodeFacade
     * @param \Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade $cartPromoCodeFacade
     */
    public function __construct(
        protected CartWatcher $cartWatcher,
        protected EntityManagerInterface $em,
        protected CurrentCustomerUser $currentCustomerUser,
        protected ProductAvailabilityFacade $productAvailabilityFacade,
        protected Domain $domain,
        protected TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        protected CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly WhateverOrderCartFacade $whateverOrderCartFacade,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function getCheckedCartWithModifications(Order $cart): CartWithModificationsResult
    {
        d('getCheckedCartWithModifications');
        // TODO tohle je zajímavý vstup - projdu všechny možné změny, ponastavuju věci na samotné Order entitě. Pak bych mohl buď za každou změnou zavolat vytvoření inputu a prohnat to přes procesor nebo to procesorem prohnat až na konci
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
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    protected function checkRemovedProductsItems(Order $cart): void
    {
        foreach ($cart->getProductItems() as $cartItem) {
            if (!$cartItem->hasProduct()) {
                $cart->removeItem($cartItem);
                $this->em->remove($cartItem);

                $this->cartWithModificationsResult->setCartHasRemovedProducts();
            }
        }
        $this->whateverOrderCartFacade->updateCartOrder($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    protected function checkModifiedPrices(Order $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        foreach ($modifiedItems as $cartItem) {
            $this->cartWithModificationsResult->addCartItemWithModifiedPrice($cartItem);
        }

        // TODO here or inside cartWatcher->getModifiedPriceItemsAndUpdatePrices()?
        $this->whateverOrderCartFacade->updateCartOrder($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    protected function checkNotListableItems(Order $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        foreach ($notVisibleItems as $cartItem) {
            $cart->removeItem($cartItem);
            $this->em->remove($cartItem);

            $this->cartWithModificationsResult->addNoLongerListableCartItem($cartItem);
        }

        $this->whateverOrderCartFacade->updateCartOrder($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    protected function checkUnavailableStockQuantityItems(Order $cart): void
    {
        foreach ($cart->getProductItems() as $cartItem) {
            $product = $cartItem->getProduct();

            if ($product === null) {
                continue;
            }

            $maximumOrderQuantity = $this->productAvailabilityFacade->getGroupedStockQuantityByProductAndDomainId($product, $this->domain->getId());

            if ($maximumOrderQuantity === 0) {
                $cart->removeItem($cartItem);
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

        $this->whateverOrderCartFacade->updateCartOrder($cart);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     */
    protected function checkPromoCodeValidity(Order $cart): void
    {
        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            try {
                $this->currentPromoCodeFacade->getValidatedPromoCode($promoCode->getCode(), $cart);
            } catch (PromoCodeException $exception) {
                $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
                $this->cartWithModificationsResult->addChangedPromoCode($promoCode->getCode());
                $this->whateverOrderCartFacade->updateCartOrder($cart);
            }
        }

    }
}
