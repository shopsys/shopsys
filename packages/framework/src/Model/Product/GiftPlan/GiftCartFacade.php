<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException;
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
     */
    protected function applyGiftCartItemSetupListToCart(Cart $cart, array $giftCartItemSetupList, int $domainId): void
    {
        foreach ($giftCartItemSetupList as $giftCartItemSetup) {
            $giftProduct = $giftCartItemSetup->getGiftProduct();

            if ($cart->isProductGiftRemoved($giftProduct, $giftCartItemSetup->getMainProduct())) {
                continue;
            }

            $giftQuantity = $giftCartItemSetup->getQuantity();

            $giftCartItems = array_filter($cart->getProductGiftCartItems(), fn (CartItem $cartItem) => $cartItem->getProduct()->getId() === $giftProduct->getId()
                && $cartItem->getGiftMainProduct()?->getId() === $giftCartItemSetup->getMainProduct()?->getId());
            $giftCartItem = array_first($giftCartItems);

            if ($giftCartItem) {
                if ($giftCartItem->getQuantity() !== $giftQuantity) {
                    $giftCartItem->changeQuantity($giftQuantity);
                    $giftCartItem->setAddedAtToNow();
                }

                continue;
            }

            $giftPrice = $this->giftPlanSettingFacade->calculateBaseGiftPrice($domainId, $giftProduct->getVatForDomain($domainId));
            $newCartItem = $this->cartItemFactory->create($cart, $giftProduct, $giftQuantity, $giftPrice, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
            $newCartItem->setGiftMainProduct($giftCartItemSetup->getMainProduct());
            $cart->addItem($newCartItem);
            $this->em->persist($newCartItem);
            $this->em->flush();
        }
    }

    /**
     * @return array<string, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup>
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

                $key = $item->getProduct()->getId() . ':' . $giftProduct->getId();
                $giftsSetup[$key] = new GiftCartItemSetup($giftProduct, $item->getQuantity(), $item->getProduct());
            }
        }

        return $giftsSetup;
    }

    /**
     * @param array<string, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup> $giftCartItemSetupList
     * @return array<string, \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup>
     */
    protected function getAvailableGiftCartItemSetupList(
        Cart $cart,
        array $giftCartItemSetupList,
        int $domainId,
    ): array {
        ksort($giftCartItemSetupList, SORT_NATURAL);
        $prioritizedSetups = [];

        // Existing gifts keep priority over newly added or restored pairs when stock is limited.
        foreach ($cart->getProductGiftCartItems() as $item) {
            $key = $item->getGiftMainProduct()?->getId() . ':' . $item->getProduct()->getId();

            if (!isset($giftCartItemSetupList[$key])) {
                continue;
            }

            $prioritizedSetups[$key] = $giftCartItemSetupList[$key];
            unset($giftCartItemSetupList[$key]);
        }

        foreach ($giftCartItemSetupList as $key => $setup) {
            $prioritizedSetups[$key] = $setup;
        }

        $allocatedQuantities = $this->getPurchasedProductQuantities($cart);
        $availableSetups = [];

        foreach ($prioritizedSetups as $key => $setup) {
            $giftProduct = $setup->getGiftProduct();

            if ($cart->isProductGiftRemoved($giftProduct, $setup->getMainProduct())) {
                continue;
            }

            $allocatedQuantity = $allocatedQuantities[$giftProduct->getId()] ?? 0;
            $quantity = $this->getAvailableGiftQuantity($giftProduct, $allocatedQuantity + $setup->getQuantity(), $domainId) - $allocatedQuantity;

            if ($quantity <= 0) {
                continue;
            }

            $availableSetups[$key] = new GiftCartItemSetup($giftProduct, $quantity, $setup->getMainProduct());
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

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartItemSetup[]
     */
    public function getRemovedGiftCartItemSetups(Cart $cart, int $domainId): array
    {
        if (!$cart->hasRemovedProductGifts()) {
            return [];
        }

        $giftCartItemSetupList = $this->getGiftCartItemSetupListByCart($cart, $domainId);
        $availableGiftCartItemSetupList = $this->getAvailableGiftCartItemSetupList($cart, $giftCartItemSetupList, $domainId);
        $allocatedQuantities = $this->getPurchasedProductQuantities($cart);

        foreach ($availableGiftCartItemSetupList as $setup) {
            $giftProductId = $setup->getGiftProduct()->getId();
            $allocatedQuantities[$giftProductId] = ($allocatedQuantities[$giftProductId] ?? 0) + $setup->getQuantity();
        }

        $removedSetups = [];

        foreach ($giftCartItemSetupList as $setup) {
            $giftProduct = $setup->getGiftProduct();

            if (!$cart->isProductGiftRemoved($giftProduct, $setup->getMainProduct())) {
                continue;
            }

            $allocatedQuantity = $allocatedQuantities[$giftProduct->getId()] ?? 0;
            $quantity = $this->getAvailableGiftQuantity($giftProduct, $allocatedQuantity + $setup->getQuantity(), $domainId) - $allocatedQuantity;

            if ($quantity > 0) {
                $removedSetups[] = new GiftCartItemSetup($giftProduct, $quantity, $setup->getMainProduct());
            }
        }

        return $removedSetups;
    }

    public function forgetRemovedProductGiftsWithoutQualifyingProduct(Cart $cart, int $domainId): void
    {
        if (!$cart->hasRemovedProductGifts()) {
            return;
        }

        $mainProductIds = array_map(fn (CartItem $cartItem) => $cartItem->getProduct()->getId(), $cart->getProductCartItems());
        $giftProductIdsIndexedByMainProductIds = $this->giftPlanFacade->findActiveGiftProductIdsByMainProductIds($mainProductIds, $domainId);

        $cart->retainRemovedProductGiftIds($giftProductIdsIndexedByMainProductIds);
    }

    public function restoreProductGiftToCart(
        Cart $cart,
        string $giftProductUuid,
        int $domainId,
        ?string $mainProductUuid = null,
    ): void {
        $restored = false;

        foreach ($this->getRemovedGiftCartItemSetups($cart, $domainId) as $setup) {
            if ($setup->getGiftProduct()->getUuid() !== $giftProductUuid
                || ($mainProductUuid !== null && $setup->getMainProductUuid() !== $mainProductUuid)
            ) {
                continue;
            }

            $cart->restoreProductGift($setup->getGiftProduct(), $setup->getMainProduct());
            $restored = true;
        }

        if (!$restored) {
            throw new InvalidCartItemException('The removed gift is not available for this cart.');
        }

        $this->refreshProductGiftsInCart($cart, $domainId);
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

    public function refreshProductGiftCartItemsAndGetInvalidItems(Cart $cart, int $domainId): array
    {
        $invalidItems = [];
        $giftCartItemSetupList = $this->getGiftCartItemSetupListByCart($cart, $domainId);
        $giftCartItemSetups = $this->getAvailableGiftCartItemSetupList($cart, $giftCartItemSetupList, $domainId);

        foreach ($cart->getProductGiftCartItems() as $item) {
            $key = $item->getGiftMainProduct()?->getId() . ':' . $item->getProduct()->getId();
            $giftCartItemSetup = $giftCartItemSetups[$key] ?? null;

            if ($giftCartItemSetup === null || $cart->isProductGiftRemoved($item->getProduct(), $item->getGiftMainProduct())) {
                $invalidItems[] = $item;

                continue;
            }
        }

        $this->applyGiftCartItemSetupListToCart($cart, $giftCartItemSetups, $domainId);

        return $invalidItems;
    }
}
