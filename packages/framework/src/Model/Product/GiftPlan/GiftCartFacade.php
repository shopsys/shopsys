<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class GiftCartFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly CartItemFactory $cartItemFactory,
        protected readonly GiftPlanFacade $giftPlanFacade,
        protected readonly GiftPlanSettingFacade $giftPlanSettingFacade,
        protected readonly CurrentCustomerUser $currentCustomerUser,
        protected readonly ProductVisibilityFacade $productVisibilityFacade,
        protected readonly ProductAvailabilityFacade $productAvailabilityFacade,
    ) {
    }

    public function refreshProductGiftsInCart(Cart $cart, int $domainId): void
    {
        $giftCartItemSetupList = $this->getGiftCartItemSetupListByCart($cart, $domainId);
        $availableGiftCartItemSetupList = $this->getAvailableGiftCartItemSetupList($cart, $giftCartItemSetupList, $domainId);

        $this->applyGiftCartItemSetupListToCart($cart, $availableGiftCartItemSetupList, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[] $giftCartItemSetupList
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[] gift items that match the setups, either updated or newly added
     */
    protected function applyGiftCartItemSetupListToCart(Cart $cart, array $giftCartItemSetupList, int $domainId): array
    {
        $matchingGiftCartItems = [];

        foreach ($giftCartItemSetupList as $giftCartItemSetup) {
            $giftProduct = $giftCartItemSetup->giftProduct;

            $giftQuantity = $giftCartItemSetup->quantity;

            $parentItem = $cart->findProductCartItem($giftCartItemSetup->mainProduct);

            if ($parentItem === null) {
                continue;
            }

            $giftCartItem = $cart->findRelatedItemByProduct($parentItem, $giftProduct, CartItemTypeEnum::TYPE_PRODUCT_GIFT);

            if ($giftCartItem) {
                if ($giftCartItem->getQuantity() !== $giftQuantity) {
                    $giftCartItem->changeQuantity($giftQuantity);
                    $giftCartItem->setAddedAtToNow();
                }

                $matchingGiftCartItems[] = $giftCartItem;

                continue;
            }

            $giftPrice = $this->giftPlanSettingFacade->calculateBaseGiftPrice($domainId, $giftProduct->getVatForDomain($domainId));
            $newCartItem = $this->cartItemFactory->create($cart, $giftProduct, $giftQuantity, $giftPrice, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
            $parentItem->addRelatedItem($newCartItem);
            $cart->addItem($newCartItem);
            $this->em->persist($newCartItem);
            $this->em->flush();
            $matchingGiftCartItems[] = $newCartItem;
        }

        return $matchingGiftCartItems;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[]
     */
    protected function getGiftCartItemSetupListByCart(Cart $cart, int $domainId): array
    {
        /** @var \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[] $giftsSetup */
        $giftsSetup = [];
        $pricingGroup = $this->currentCustomerUser->getPricingGroup();

        $mainProducts = array_map(fn (CartItem $cartItem) => $cartItem->getProduct(), $cart->getProductCartItems());
        $giftProductsIndexedByMainProductIds = $this->giftPlanFacade->findActiveGiftProductsByMainProducts($mainProducts, $domainId);

        foreach ($cart->getProductCartItems() as $item) {
            $giftProducts = $giftProductsIndexedByMainProductIds[$item->getProduct()->getId()] ?? [];

            foreach ($giftProducts as $giftProduct) {
                if (!$this->isGiftProductSellable($giftProduct, $pricingGroup, $item->getQuantity(), $domainId)) {
                    continue;
                }

                $giftsSetup[] = new GiftCartItemSetup($item->getProduct(), $giftProduct, $item->getQuantity());
            }
        }

        return $giftsSetup;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[] $giftCartItemSetupList
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[]
     */
    protected function getAvailableGiftCartItemSetupList(
        Cart $cart,
        array $giftCartItemSetupList,
        int $domainId,
    ): array {
        $setupsWithGiftCartItem = [];
        $setupsWithoutGiftCartItem = [];

        // Existing gifts keep priority over newly added or restored pairs when stock is limited.
        foreach ($giftCartItemSetupList as $setup) {
            if ($this->findGiftCartItem($cart, $setup) !== null) {
                $setupsWithGiftCartItem[] = $setup;
            } else {
                $setupsWithoutGiftCartItem[] = $setup;
            }
        }

        $prioritizedSetups = [...$setupsWithGiftCartItem, ...$setupsWithoutGiftCartItem];
        $allocatedQuantities = $this->getPurchasedProductQuantities($cart);
        $availableSetups = [];

        foreach ($prioritizedSetups as $setup) {
            $giftProduct = $setup->giftProduct;

            $allocatedQuantity = $allocatedQuantities[$giftProduct->getId()] ?? 0;
            $quantity = $this->getAvailableGiftQuantity($giftProduct, $allocatedQuantity + $setup->quantity, $domainId) - $allocatedQuantity;

            if ($quantity <= 0) {
                continue;
            }

            $availableSetups[] = new GiftCartItemSetup($setup->mainProduct, $giftProduct, $quantity);
            $allocatedQuantities[$giftProduct->getId()] = $allocatedQuantity + $quantity;
        }

        return $availableSetups;
    }

    /**
     * @return array<int, int>
     */
    protected function getPurchasedProductQuantities(Cart $cart): array
    {
        $quantities = [];

        foreach ($cart->getProductCartItems() as $item) {
            $productId = $item->getProduct()->getId();
            $quantities[$productId] = ($quantities[$productId] ?? 0) + $item->getQuantity();
        }

        return $quantities;
    }

    public function isGiftProductSellable(
        Product $product,
        PricingGroup $pricingGroup,
        int $quantity,
        int $domainId,
    ): bool {
        if ($product->getProductType() === ProductTypeEnum::TYPE_INQUIRY) {
            return false;
        }

        $availableQuantity = $this->getAvailableGiftQuantity($product, $quantity, $domainId);

        if ($availableQuantity <= 0) {
            return false;
        }

        $productVisibility = $this->productVisibilityFacade
            ->getProductVisibility(
                $product,
                $pricingGroup,
                $domainId,
            );

        return $productVisibility->isVisible() && !$product->isCalculatedSellingDenied($domainId);
    }

    protected function getAvailableGiftQuantity(Product $giftProduct, int $quantity, int $domainId): int
    {
        if ($giftProduct->isAllowedNegativeStock()) {
            return $quantity;
        }

        $notOnStockQuantity = $this->productAvailabilityFacade->getNotOnStockQuantity($giftProduct, $domainId, $quantity) ?? 0;

        return $quantity - $notOnStockQuantity;
    }

    protected function findGiftCartItem(Cart $cart, GiftCartItemSetup $giftCartItemSetup): ?CartItem
    {
        $parentItem = $cart->findProductCartItem($giftCartItemSetup->mainProduct);

        return $parentItem !== null
            ? $cart->findRelatedItemByProduct($parentItem, $giftCartItemSetup->giftProduct, CartItemTypeEnum::TYPE_PRODUCT_GIFT)
            : null;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem[]
     */
    public function refreshProductGiftCartItemsAndGetInvalidItems(Cart $cart, int $domainId): array
    {
        $giftCartItemSetupList = $this->getGiftCartItemSetupListByCart($cart, $domainId);
        $availableGiftCartItemSetupList = $this->getAvailableGiftCartItemSetupList($cart, $giftCartItemSetupList, $domainId);
        $matchingGiftCartItems = $this->applyGiftCartItemSetupListToCart($cart, $availableGiftCartItemSetupList, $domainId);

        return array_values(array_filter(
            $cart->getProductGiftCartItems(),
            static fn (CartItem $item) => !in_array($item, $matchingGiftCartItems, true),
        ));
    }
}
