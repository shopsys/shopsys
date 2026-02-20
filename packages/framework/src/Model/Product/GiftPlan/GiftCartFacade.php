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
        $giftPrice = $this->giftPlanSettingFacade->getInputGiftPrice($domainId);

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

            $newCartItem = $this->cartItemFactory->create($cart, $giftProduct, $giftQuantity, $giftPrice, CartItemTypeEnum::TYPE_PRODUCT_GIFT);
            $cart->addItem($newCartItem);
            $cart->setModifiedNow();
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
        if ($product->getProductType() === ProductTypeEnum::TYPE_INQUIRY) {
            return false;
        }

        if (!$product->isAllowedNegativeStock()) {
            $notOnStockQuantity = $this->productAvailabilityFacade->getNotOnStockQuantity(
                $product,
                $domainId,
                $quantity,
            ) ?? 0;

            if ($notOnStockQuantity > 0 && $quantity - $notOnStockQuantity <= 0) {
                return false;
            }
        }

        $productVisibility = $this->productVisibilityFacade
            ->getProductVisibility(
                $product,
                $pricingGroup,
                $domainId,
            );

        return $productVisibility->isVisible() && !$product->isCalculatedSellingDenied($domainId);
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
