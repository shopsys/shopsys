<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Model\Cart;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\OrderDataFactory;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\Exception\PromoCodeException;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrontendApiBundle\Model\Order\PromoCode\PromoCodeWithDiscount;

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
     * @param \Shopsys\FrameworkBundle\Model\Order\OrderDataFactory $orderDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade $promoCodeFacade
     * @param \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResultFactory $cartWithModificationsResultFactory
     */
    public function __construct(
        protected readonly CartWatcher $cartWatcher,
        protected readonly EntityManagerInterface $em,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
        protected readonly Domain $domain,
        protected readonly TransportAndPaymentWatcherFacade $transportAndPaymentWatcherFacade,
        protected readonly CurrentPromoCodeFacade $currentPromoCodeFacade,
        protected readonly CartPromoCodeFacade $cartPromoCodeFacade,
        protected readonly OrderDataFactory $orderDataFactory,
        protected readonly PromoCodeFacade $promoCodeFacade,
        protected readonly CartWithModificationsResultFactory $cartWithModificationsResultFactory,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrontendApiBundle\Model\Cart\CartWithModificationsResult
     */
    public function getCheckedCartWithModifications(Cart $cart): CartWithModificationsResult
    {
        $this->cartWithModificationsResult = $this->cartWithModificationsResultFactory->create($cart);

        $this->checkRemovedProductsItems($cart);
        $this->checkNotListableItems($cart);
        $this->checkModifiedPrices($cart);
        $this->checkPromoCodeValidity($cart);

        $this->em->flush();

        $this->transportAndPaymentWatcherFacade->checkTransportAndPayment($this->cartWithModificationsResult, $cart);
        $this->setPromoCodesWithAppliedDiscount($cart);

        return $this->cartWithModificationsResult;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkModifiedPrices(Cart $cart): void
    {
        $modifiedItems = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);

        foreach ($modifiedItems as $cartItem) {
            $this->cartWithModificationsResult->addCartItemWithModifiedPrice($cartItem);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function checkNotListableItems(Cart $cart): void
    {
        $notVisibleItems = $this->cartWatcher->getNotListableItems($cart, $this->currentCustomerUser);

        foreach ($notVisibleItems as $cartItem) {
            $cart->removeItemById($cartItem->getId());
            $this->em->remove($cartItem);

            $this->cartWithModificationsResult->addNoLongerListableCartItem($cartItem);
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     */
    protected function setPromoCodesWithAppliedDiscount(Cart $cart): void
    {
        $orderData = $this->orderDataFactory->createFromCart($cart, $this->domain->getCurrentDomainConfig());

        if ($orderData->promoCode === null) {
            return;
        }

        foreach ($cart->getAllAppliedPromoCodes() as $promoCode) {
            if ($promoCode->getCode() !== $orderData->promoCode) {
                continue;
            }

            if ($promoCode->isFreeTransportAndPaymentType()) {
                $discount = new Price(Money::zero(), Money::zero());
            } elseif ($promoCode->getDiscountType() === PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL) {
                $discount = $orderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]);
            } else {
                $promoCodeLimit = $this->promoCodeFacade->getHighestLimitByPromoCodeAndTotalPrice($promoCode, $this->cartWithModificationsResult->getTotalItemsPrice());

                $discount = new Price(
                    Money::create($promoCodeLimit->getDiscount()),
                    Money::create($promoCodeLimit->getDiscount()),
                );
            }

            $this->cartWithModificationsResult->addPromoCodeWithAppliedDiscount(
                new PromoCodeWithDiscount(
                    $promoCode,
                    $discount,
                ),
            );
        }
    }
}
