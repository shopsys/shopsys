<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\OrderFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
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
        protected readonly TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly OrderFacade $orderFacade,
        protected readonly CartWithModificationsResultFactory $cartWithModificationsResultFactory,
        protected readonly GiftCartFacade $giftCartFacade,
        protected readonly AdditionalServiceFacade $additionalServiceFacade,
        protected readonly AdditionalServicePriceCalculation $additionalServicePriceCalculation,
    ) {
    }

    public function getCheckedCartWithModifications(Cart $cart): CartWithModificationsResult
    {
        $this->cartWithModificationsResult = $this->cartWithModificationsResultFactory->create($cart);

        $this->checkRemovedProductsItems($cart);
        $this->checkNotListableItems($cart);
        $this->checkProductGiftCartItemsValidity($cart);
        $this->checkModifiedPrices($cart);
        $this->checkStockQuantities($cart);
        $this->checkAdditionalServicesValidity($cart);
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

    protected function checkAdditionalServicesValidity(Cart $cart): void
    {
        $productIdsWithAdditionalServices = [];

        foreach ($cart->getItems() as $cartItem) {
            if ($this->isCartItemSubjectToAdditionalServicesCheck($cartItem)) {
                $productIdsWithAdditionalServices[] = $cartItem->getProduct()->getId();
            }
        }

        if ($productIdsWithAdditionalServices === []) {
            return;
        }

        $offeredAdditionalServicesIndexedByProductId = $this->additionalServiceFacade->getEnabledIndexedByProductIds(
            $productIdsWithAdditionalServices,
            $this->domain->getId(),
        );

        foreach ($cart->getItems() as $cartItem) {
            if (!$this->isCartItemSubjectToAdditionalServicesCheck($cartItem)) {
                continue;
            }

            $this->checkCartItemAdditionalServices(
                $cartItem,
                $offeredAdditionalServicesIndexedByProductId[$cartItem->getProduct()->getId()] ?? [],
            );
        }
    }

    protected function isCartItemSubjectToAdditionalServicesCheck(CartItem $cartItem): bool
    {
        return $this->canCartItemCarryAdditionalServices($cartItem)
            && ($cartItem->getAdditionalServices() !== [] || $cartItem->getWatchedAdditionalServicePrices() !== []);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $offeredAdditionalServices
     */
    protected function checkCartItemAdditionalServices(CartItem $cartItem, array $offeredAdditionalServices): void
    {
        $chosenAdditionalServices = $cartItem->getAdditionalServices();
        $offeredAdditionalServiceIds = array_map(
            static fn (AdditionalService $offeredAdditionalService) => $offeredAdditionalService->getId(),
            $offeredAdditionalServices,
        );
        $stillOfferedAdditionalServices = array_values(array_filter(
            $chosenAdditionalServices,
            static fn (AdditionalService $additionalService) => in_array($additionalService->getId(), $offeredAdditionalServiceIds, true),
        ));

        if ($this->hasCartItemRemovedAdditionalServices($cartItem, $stillOfferedAdditionalServices)) {
            $this->cartWithModificationsResult->addCartItemWithRemovedAdditionalServices($cartItem);
        }

        if (count($stillOfferedAdditionalServices) !== count($chosenAdditionalServices)) {
            $cartItem->setAdditionalServices($stillOfferedAdditionalServices);
        }

        $this->watchAdditionalServicePrices($cartItem, $stillOfferedAdditionalServices);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $stillOfferedAdditionalServices
     */
    protected function hasCartItemRemovedAdditionalServices(
        CartItem $cartItem,
        array $stillOfferedAdditionalServices,
    ): bool {
        $chosenAdditionalServices = $cartItem->getAdditionalServices();

        if (count($stillOfferedAdditionalServices) !== count($chosenAdditionalServices)) {
            return true;
        }

        $chosenAdditionalServiceIds = array_map(
            static fn (AdditionalService $additionalService) => $additionalService->getId(),
            $chosenAdditionalServices,
        );

        return array_any(
            array_keys($cartItem->getWatchedAdditionalServicePrices()),
            static fn (int $watchedAdditionalServiceId): bool => !in_array($watchedAdditionalServiceId, $chosenAdditionalServiceIds, true),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[] $stillOfferedAdditionalServices
     */
    protected function watchAdditionalServicePrices(CartItem $cartItem, array $stillOfferedAdditionalServices): void
    {
        $watchedAdditionalServicePrices = $cartItem->getWatchedAdditionalServicePrices();
        $newWatchedAdditionalServicePrices = [];
        $hasModifiedAdditionalServicePrice = false;

        foreach ($stillOfferedAdditionalServices as $additionalService) {
            $priceWithVat = $this->additionalServicePriceCalculation
                ->calculatePrice($additionalService, $cartItem->getProduct(), $this->domain->getId())
                ->getPriceWithVat();
            $watchedAdditionalServicePrice = $watchedAdditionalServicePrices[$additionalService->getId()] ?? null;

            if (
                $watchedAdditionalServicePrice !== null
                && !$priceWithVat->equals(Money::create($watchedAdditionalServicePrice))
            ) {
                $hasModifiedAdditionalServicePrice = true;
            }

            $newWatchedAdditionalServicePrices[$additionalService->getId()] = $priceWithVat->getAmount();
        }

        if ($hasModifiedAdditionalServicePrice) {
            $this->cartWithModificationsResult->addCartItemWithModifiedAdditionalServicePrices($cartItem);
        }

        $cartItem->setWatchedAdditionalServicePrices($newWatchedAdditionalServicePrices);
    }

    protected function canCartItemCarryAdditionalServices(CartItem $cartItem): bool
    {
        return $cartItem->getType() === CartItemTypeEnum::TYPE_PRODUCT && $cartItem->hasProduct();
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
}
