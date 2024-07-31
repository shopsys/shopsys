<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CartWatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade $productVisibilityFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(
        protected readonly ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly Domain $domain,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getModifiedPriceItemsAndUpdatePrices(Order $cart): array
    {
        $modifiedItems = [];

        foreach ($cart->getProductItems() as $cartItem) {
            $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser(
                $cartItem->getProduct(),
            );

            if (!$productPrice->equals($cartItem->getPrice())) {
                $modifiedItems[] = $cartItem;
            }
            $cartItem->setUnitPrice($productPrice);
        }

        return $modifiedItems;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $cart
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser $currentCustomerUser
     * @return \Shopsys\FrameworkBundle\Model\Order\Item\OrderItem[]
     */
    public function getNotListableItems(Order $cart, CurrentCustomerUser $currentCustomerUser): array
    {
        $notListableItems = [];

        foreach ($cart->getProductItems() as $item) {
            try {
                /** @var \Shopsys\FrameworkBundle\Model\Product\Product $product */
                $product = $item->getProduct();
                $productVisibility = $this->productVisibilityFacade
                    ->getProductVisibility(
                        $product,
                        $currentCustomerUser->getPricingGroup(),
                        $this->domain->getId(),
                    );

                if (!$productVisibility->isVisible() || $product->getCalculatedSellingDenied()) {
                    $notListableItems[] = $item;

                    continue;
                }

                $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser(
                    $product,
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
