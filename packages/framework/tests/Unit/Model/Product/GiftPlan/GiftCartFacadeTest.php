<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
use Shopsys\FrameworkBundle\Model\Cart\CartRemovedProductGift;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactory;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Availability\ProductAvailabilityFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftCartFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductTypeEnum;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

final class GiftCartFacadeTest extends TestCase
{
    public function testSharedStockIsAllocatedOnlyOnceAcrossGiftRowsAndPaidProduct(): void
    {
        $firstProduct = $this->createProduct(1);
        $secondProduct = $this->createProduct(2);
        $gift = $this->createProduct(3);
        $cart = $this->createCart([$firstProduct, $secondProduct, $gift], [5, 3, 1]);
        $facade = $this->createGiftCartFacade([1 => [$gift], 2 => [$gift]], 7);

        $facade->refreshProductGiftsInCart($cart, 1);

        $items = array_values($cart->getProductGiftCartItems());
        self::assertCount(2, $items);
        self::assertSame([5, 1], array_map(static fn (CartItem $item) => $item->getQuantity(), $items));
        self::assertSame([$firstProduct, $secondProduct], array_map(static fn (CartItem $item) => $cart->findParentItem($item)->getProduct(), $items));
    }

    public function testExistingGiftKeepsStockWhenAnotherProductQualifiesForTheSameGift(): void
    {
        $firstProduct = $this->createProduct(1);
        $secondProduct = $this->createProduct(2);
        $gift = $this->createProduct(3);
        $cart = $this->createCart([$firstProduct, $secondProduct], [5, 3]);
        $this->createGiftCartFacade([2 => [$gift]], 6)->refreshProductGiftsInCart($cart, 1);

        $this->createGiftCartFacade([1 => [$gift], 2 => [$gift]], 6)->refreshProductGiftsInCart($cart, 1);

        $items = array_values($cart->getProductGiftCartItems());
        self::assertSame([$secondProduct, $firstProduct], array_map(static fn (CartItem $item) => $cart->findParentItem($item)->getProduct(), $items));
        self::assertSame([3, 3], array_map(static fn (CartItem $item) => $item->getQuantity(), $items));
    }

    public function testRemovedGiftStaysRemovedWhenItsStockIsZero(): void
    {
        $firstProduct = $this->createProduct(1);
        $gift = $this->createProduct(3);
        $cart = $this->createCart([$firstProduct], [1]);
        $this->markGiftRemoved($cart, $gift, $firstProduct);
        $facade = $this->createGiftCartFacade([1 => [$gift]], 0);

        $facade->refreshProductGiftsInCart($cart, 1);

        self::assertSame([], $facade->getRestorableRemovedProductGifts($cart, 1));
        self::assertTrue($cart->isProductGiftRemoved($firstProduct, $gift));
    }

    private function createProduct(int $id): Product
    {
        $product = $this->createStub(Product::class);
        $product->method('getId')->willReturn($id);
        $product->method('getUuid')->willReturn('product-' . $id);
        $product->method('getProductType')->willReturn(ProductTypeEnum::TYPE_BASIC);
        $product->method('getVatForDomain')->willReturn($this->createStub(Vat::class));

        return $product;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param int[] $quantities
     */
    private function createCart(array $products, array $quantities): Cart
    {
        $identifier = $this->createStub(CustomerUserIdentifier::class);
        $identifier->method('getCartIdentifier')->willReturn('independent-gifts');
        $cart = (new CartFactory(new EntityNameResolver([])))->create($identifier);

        foreach ($products as $i => $product) {
            $cart->addItem($this->createCartItemFactory()->create($cart, $product, $quantities[$i], null, CartItemTypeEnum::TYPE_PRODUCT));
        }

        return $cart;
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]> $giftsByParent
     */
    private function createGiftCartFacade(array $giftsByParent, int $stock = 100): GiftCartFacade
    {
        $planFacade = $this->createStub(GiftPlanFacade::class);
        $planFacade->method('findActiveGiftProductsByMainProducts')->willReturn($giftsByParent);
        $planFacade->method('findActiveGiftProductIdsByMainProductIds')->willReturnCallback(
            static fn (array $mainProductIds) => array_map(
                static fn (array $gifts) => array_map(static fn (Product $gift) => $gift->getId(), $gifts),
                array_intersect_key($giftsByParent, array_flip($mainProductIds)),
            ),
        );
        $currentUser = $this->createStub(CurrentCustomerUser::class);
        $currentUser->method('getPricingGroup')->willReturn($this->createStub(PricingGroup::class));
        $visibility = $this->createStub(ProductVisibility::class);
        $visibility->method('isVisible')->willReturn(true);
        $visibilityFacade = $this->createStub(ProductVisibilityFacade::class);
        $visibilityFacade->method('getProductVisibility')->willReturn($visibility);
        $availabilityFacade = $this->createStub(ProductAvailabilityFacade::class);
        $availabilityFacade->method('getNotOnStockQuantity')->willReturnCallback(static fn (Product $product, int $domain, int $quantity) => max(0, $quantity - $stock));
        $setting = $this->createStub(GiftPlanSettingFacade::class);
        $setting->method('calculateBaseGiftPrice')->willReturn(Price::zero());

        return new GiftCartFacade($this->createStub(EntityManagerInterface::class), $this->createCartItemFactory(), $planFacade, $setting, $currentUser, $visibilityFacade, $availabilityFacade);
    }

    private function createCartItemFactory(): CartItemFactory
    {
        return new CartItemFactory(new EntityNameResolver([]), new CartItemTypeEnum());
    }

    private function markGiftRemoved(Cart $cart, Product $gift, Product $mainProduct): void
    {
        $cart->addRemovedProductGift(new CartRemovedProductGift($cart, $mainProduct, $gift));
    }
}
