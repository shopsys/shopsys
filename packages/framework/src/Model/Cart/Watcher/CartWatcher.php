<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CartWatcher
{
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly Domain $domain,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getModifiedPriceItemsAndUpdatePrices(Cart $cart)
    {
        $modifiedItems = [];

        foreach ($cart->getProductCartItems() as $cartItem) {
            $price = $this->productPriceCalculationForCustomerUser->calculatePricesForCurrentUser(
                $cartItem->getProduct(),
            )->sellingProductPrice->getPrice();

            if (!$price->getPriceWithVat()->equals($cartItem->getWatchedPrice() ?? Money::zero())) {
                $modifiedItems[] = $cartItem;
            }
            $cartItem->setWatchedPrice($price->getPriceWithVat());
        }

        $giftPrice = $this->giftPlanSettingFacade->getInputGiftPrice($this->domain->getId());

        foreach ($cart->getProductGiftCartItems() as $cartItem) {
            if ($giftPrice->equals($cartItem->getWatchedPrice() ?? Money::zero())) {
                continue;
            }

            $cartItem->setWatchedPrice($giftPrice);
        }

        return $modifiedItems;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getNotListableItems(Cart $cart, CurrentCustomerUser $currentCustomerUser)
    {
        $notListableItems = [];

        foreach ($cart->getItems() as $item) {
            try {
                $product = $item->getProduct();

                if ($product->getProductType() === ProductTypeEnum::TYPE_INQUIRY) {
                    $notListableItems[] = $item;

                    continue;
                }

                $productVisibility = $this->productVisibilityFacade
                    ->getProductVisibility(
                        $product,
                        $currentCustomerUser->getPricingGroup(),
                        $this->domain->getId(),
                    );

                if (!$productVisibility->isVisible() || $product->isCalculatedSellingDenied($this->domain->getId())) {
                    $notListableItems[] = $item;

                    continue;
                }

                $productPrice = $this->productPriceCalculationForCustomerUser
                    ->calculatePricesForCurrentUser($product)
                    ->sellingProductPrice;

                if ($productPrice->getPrice()->getPriceWithVat()->equals(Money::zero())) {
                    $notListableItems[] = $item;
                }
            } catch (ProductNotFoundException $e) {
                $notListableItems[] = $item;
            }
        }

        return $notListableItems;
    }
}
