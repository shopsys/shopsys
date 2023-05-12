<?php

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

class CartWatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository $productVisibilityRepository
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ProductVisibilityRepository $productVisibilityRepository,
        protected readonly Domain $domain
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getModifiedPriceItemsAndUpdatePrices(Cart $cart)
    {
        $modifiedItems = [];

        foreach ($cart->getItems() as $cartItem) {
            $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser(
                $cartItem->getProduct()
            );

            if (!$productPrice->getPriceWithVat()->equals($cartItem->getWatchedPrice())) {
                $modifiedItems[] = $cartItem;
            }
            $cartItem->setWatchedPrice($productPrice->getPriceWithVat());
        }

        return $modifiedItems;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Cart\Cart $cart
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function getNotListableItems(Cart $cart, CurrentCustomerUser $currentCustomerUser)
    {
        $notListableItems = [];

        foreach ($cart->getItems() as $item) {
            try {
                $product = $item->getProduct();
                $productVisibility = $this->productVisibilityRepository
                    ->getProductVisibility(
                        $product,
                        $currentCustomerUser->getPricingGroup(),
                        $this->domain->getId()
                    );

                if (!$productVisibility->isVisible() || $product->getCalculatedSellingDenied()) {
                    $notListableItems[] = $item;

                    continue;
                }

                $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser(
                    $product
                );

                if ($productPrice->getPriceWithVat()->equals(Money::zero())) {
                    $notListableItems[] = $item;
                }
            } catch (ProductNotFoundException $e) {
                $notListableItems[] = $item;
            }
        }

        return $notListableItems;
    }
}
