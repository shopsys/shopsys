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
        $cartItemSetupList = $this->getGiftCartItemSetupListByCart($cart, $domainId);

        foreach ($cartItemSetupList as $giftCartItemSetup) {
            $giftProduct = $giftCartItemSetup->getGiftProduct();
            $giftQuantity = $giftCartItemSetup->getQuantity();

            $giftCartItems = array_filter($cart->getProductGiftCartItems(), fn (CartItem $cartItem) => $cartItem->getProduct()->getId() === $giftProduct->getId());
            $giftCartItem = array_first($giftCartItems);

            if ($giftCartItem) {
                $giftCartItem->changeQuantity($giftQuantity);
                $giftCartItem->setAddedAtToNow();

                continue;
            }

            $giftPrice = $this->giftPlanSettingFacade->calculateBaseGiftPrice($domainId, $giftProduct->getVatForDomain($domainId));
            $newCartItem = $this->cartItemFactory->create($cart, $giftProduct, $giftQuantity, $giftPrice, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
            $cart->addItem($newCartItem);
            $this->em->persist($newCartItem);
            $this->em->flush();
        }
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

                if (!array_key_exists($giftProduct->getId(), $giftsSetup)) {
                    $giftsSetup[$giftProduct->getId()] = new GiftCartItemSetup($giftProduct, $item->getQuantity());
                } else {
                    $giftsSetup[$giftProduct->getId()]->addQuantity($item->getQuantity());
                }
            }
        }

        return $giftsSetup;
    }

    public function isGiftProductSellable(
        Product $product,
        PricingGroup $pricingGroup,
        int $quantity,
        int $domainId,
    ): bool {
        return $this->getSellableGiftProductIds([$product], $pricingGroup, $quantity, $domainId) !== [];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $giftProducts
     * @return int[]
     */
    public function getSellableGiftProductIds(
        array $giftProducts,
        PricingGroup $pricingGroup,
        int $quantity,
        int $domainId,
    ): array {
        $orderableGiftProducts = array_filter(
            $giftProducts,
            static fn (Product $giftProduct): bool => $giftProduct->getProductType() !== ProductTypeEnum::TYPE_INQUIRY,
        );
        $giftProductsOnStock = $this->getGiftProductsOnStock($orderableGiftProducts, $quantity, $domainId);

        if ($giftProductsOnStock === []) {
            return [];
        }

        if (count($giftProductsOnStock) > 1) {
            $this->productVisibilityFacade->preloadProductVisibilitiesByProductIds(
                array_map(static fn (Product $giftProduct): int => $giftProduct->getId(), $giftProductsOnStock),
                $pricingGroup,
                $domainId,
            );
        }

        $sellableGiftProductIds = [];

        foreach ($giftProductsOnStock as $giftProduct) {
            $productVisibility = $this->productVisibilityFacade->getProductVisibility($giftProduct, $pricingGroup, $domainId);

            if ($productVisibility->isVisible() && !$giftProduct->isCalculatedSellingDenied($domainId)) {
                $sellableGiftProductIds[] = $giftProduct->getId();
            }
        }

        return $sellableGiftProductIds;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $giftProducts
     * @return \Shopsys\FrameworkBundle\Model\Product\Product[]
     */
    protected function getGiftProductsOnStock(array $giftProducts, int $quantity, int $domainId): array
    {
        $giftProductsWithLimitedStock = array_values(array_filter(
            $giftProducts,
            static fn (Product $giftProduct): bool => !$giftProduct->isAllowedNegativeStock(),
        ));
        $notOnStockQuantitiesIndexedByProductId = $this->productAvailabilityFacade->getNotOnStockQuantitiesIndexedByProductId(
            $giftProductsWithLimitedStock,
            $domainId,
            $quantity,
        );

        return array_values(array_filter(
            $giftProducts,
            static function (Product $giftProduct) use ($notOnStockQuantitiesIndexedByProductId, $quantity): bool {
                $notOnStockQuantity = $notOnStockQuantitiesIndexedByProductId[$giftProduct->getId()] ?? 0;

                return $notOnStockQuantity <= 0 || $quantity - $notOnStockQuantity > 0;
            },
        ));
    }

    public function refreshProductGiftCartItemsAndGetInvalidItems(Cart $cart, int $domainId): array
    {
        $invalidItems = [];
        $giftCartItemSetups = $this->getGiftCartItemSetupListByCart($cart, $domainId);

        foreach ($cart->getProductGiftCartItems() as $item) {
            $giftCartItemSetup = $giftCartItemSetups[$item->getProduct()->getId()] ?? null;

            if ($giftCartItemSetup === null) {
                $invalidItems[] = $item;

                continue;
            }

            $giftQuantity = $giftCartItemSetup->getQuantity();

            if ($giftQuantity === $item->getQuantity()) {
                continue;
            }

            $item->changeQuantity($giftQuantity);
            $item->setAddedAtToNow();
            $this->em->flush();
        }

        return $invalidItems;
    }
}
