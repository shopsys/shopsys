<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade;

class CartWatcherFacade
{
    protected CartWithModificationsResult $cartWithModificationsResult;

    public function __construct(
        protected readonly CartWatcher $cartWatcher,
        protected readonly EntityManagerInterface $em,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly Domain $domain,
        protected readonly CurrentCurrencyProvider $currentCurrencyProvider,
        protected readonly TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly CartWithModificationsResultFactory $cartWithModificationsResultFactory,
        protected readonly GiftCartFacade $giftCartFacade,
    ) {
    }

    public function getCheckedCartWithModifications(Cart $cart): CartWithModificationsResult
    {
        $this->resetWatchedPricesOnCurrencyChange($cart);

        $this->cartWithModificationsResult = $this->cartWithModificationsResultFactory->create($cart);

        $this->checkRemovedProductsItems($cart);
        $this->checkNotListableItems($cart);
        $this->checkProductGiftCartItemsValidity($cart);
        $this->checkModifiedPrices($cart);
        $this->checkStockQuantities($cart);
        $this->checkPromoCodeValidity($cart);

        $this->em->flush();

        $this->transportAndPaymentWatcherFacade->checkTransportAndPayment($this->cartWithModificationsResult, $cart);
        $this->setPromoCodes($cart);

        return $this->cartWithModificationsResult;
    }

    protected function checkRemovedProductsItems(Cart $cart): void
    {
        foreach ($cart->getItems() as $cartItem) {
            if (!$cartItem->hasProduct()) {
                $cart->removeItemById($cartItem->getId());
                $this->em->remove($cartItem);

                $this->cartWithModificationsResult->setCartHasRemovedProducts();
            }
        }
    }

    protected function checkModifiedPrices(Cart $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        foreach ($modifiedItems as $cartItem) {
            $this->cartWithModificationsResult->addCartItemWithModifiedPrice($cartItem);
        }
    }

    protected function checkNotListableItems(Cart $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        foreach ($notVisibleItems as $cartItem) {
            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);

            $this->cartWithModificationsResult->addNoLongerListableCartItem($cartItem);
        }
    }

    protected function checkProductGiftCartItemsValidity(Cart $cart): void
    {
        $removedItems = $this->giftCartFacade->refreshProductGiftCartItemsAndGetInvalidItems($cart, $this->domain->getId());

        foreach ($removedItems as $cartItem) {
            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);
            $this->em->flush();
        }
    }

    protected function checkPromoCodeValidity(Cart $cart): void
    {
        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            try {
                $this->currentPromoCodeFacade->getValidatedPromoCode($promoCode->getCode(), $cart);
            } catch (PromoCodeException) {
                $this->cartPromoCodeFacade->removePromoCode($cart, $promoCode);
                $this->cartWithModificationsResult->addChangedPromoCode($promoCode->getCode());
            }
        }
    }

    protected function checkStockQuantities(Cart $cart): void
    {
        foreach ($cart->getItems() as $cartItem) {
            $product = $cartItem->getProduct();

            if ($product === null || $product->isAllowedNegativeStock()) {
                continue;
            }

            $currentQuantity = $cartItem->getQuantity();
            $notOnStockQuantity = $this->productAvailabilityFacade->getNotOnStockQuantity($product, $this->domain->getId(), $currentQuantity) ?? 0;

            if ($notOnStockQuantity <= 0) {
                continue;
            }

            $newQuantity = $currentQuantity - $notOnStockQuantity;

            if ($newQuantity <= 0) {
                $cart->removeItemById($cartItem->getId());
                $this->em->remove($cartItem);

                if ($cartItem->isProduct()) {
                    $this->cartWithModificationsResult->setCartHasRemovedProducts();
                }
            } else {
                $cartItem->changeQuantity($newQuantity);

                if ($cartItem->isProduct()) {
                    $this->cartWithModificationsResult->addCartItemWithChangedQuantity($cartItem);
                }
            }
        }
    }

    protected function setPromoCodes(Cart $cart): void
    {
        $orderData = $this->orderFacade->createOrderDataFromCart($cart, $this->domain->getCurrentDomainConfig());

        if ($orderData->promoCode === null) {
            return;
        }

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            if ($promoCode->getCode() !== $orderData->promoCode) {
                continue;
            }

            /*
                when we add support for multiple promo codes,
                this line should be replaced with the calculation of price for the specific promo code
            */
            $promoCodeDiscountPrice = $orderData->getPromoCodeDiscountPrice();
            $this->cartWithModificationsResult->addPromoCode($promoCode, $promoCodeDiscountPrice);
        }
    }

    /**
     * The watched prices are stored in a single currency, comparing them after a currency switch would report
     * false price changes, so they are reset and watched anew in the current currency
     */
    protected function resetWatchedPricesOnCurrencyChange(Cart $cart): void
    {
        $currentCurrencyCode = $this->currentCurrencyProvider->getCurrentCurrencyOfDomain($this->domain->getId())->getCode();

        if ($cart->getCurrencyCode() === $currentCurrencyCode) {
            return;
        }

        foreach ($cart->getItems() as $cartItem) {
            $cartItem->setWatchedPrice(null);
        }

        $cart->setTransportWatchedPrice(null);
        $cart->setPaymentWatchedPrice(null);
        $cart->setCurrencyCode($currentCurrencyCode);
        $this->em->flush();
    }
}
