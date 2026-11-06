<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\GiftPlan;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\CartFactory;
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
        $first = $this->product(1);
        $second = $this->product(2);
        $gift = $this->product(3);
        $cart = $this->cart([$first, $second, $gift], [5, 3, 1]);
        $facade = $this->facade([1 => [$gift], 2 => [$gift]], 7);

        $facade->refreshProductGiftsInCart($cart, 1);

        $items = array_values($cart->getProductGiftCartItems());
        self::assertCount(2, $items);
        self::assertSame([5, 1], array_map(static fn (CartItem $item) => $item->getQuantity(), $items));
        self::assertSame([$first, $second], array_map(static fn (CartItem $item) => $item->getGiftMainProduct(), $items));
    }

    public function testRemovingOneGiftDoesNotSuppressOtherParentOrOtherGift(): void
    {
        $first = $this->product(1);
        $second = $this->product(2);
        $gift = $this->product(3);
        $otherGift = $this->product(4);
        $cart = $this->cart([$first, $second], [5, 3]);
        $cart->markProductGiftRemoved($gift, $first);
        $facade = $this->facade([1 => [$gift, $otherGift], 2 => [$gift]]);

        $facade->refreshProductGiftsInCart($cart, 1);

        $items = array_values($cart->getProductGiftCartItems());
        self::assertCount(2, $items);
        self::assertSame($otherGift, $items[0]->getProduct());
        self::assertSame($second, $items[1]->getGiftMainProduct());
        $removed = $facade->getRemovedGiftCartItemSetups($cart, 1);
        self::assertCount(1, $removed);
        self::assertSame($first, $removed[0]->getMainProduct());
        self::assertSame(5, $removed[0]->getQuantity());
    }

    public function testRestoringOnePairPreservesOtherRemovalAndExistingStockAllocation(): void
    {
        $first = $this->product(1);
        $second = $this->product(2);
        $gift = $this->product(3);
        $cart = $this->cart([$first, $second], [5, 3]);
        $cart->markProductGiftRemoved($gift, $first);
        $cart->markProductGiftRemoved($gift, $second);
        $facade = $this->facade([1 => [$gift], 2 => [$gift]], 6);

        $facade->restoreProductGiftToCart($cart, $gift->getUuid(), 1, $second->getUuid());

        self::assertTrue($cart->isProductGiftRemoved($gift, $first));
        self::assertFalse($cart->isProductGiftRemoved($gift, $second));
        self::assertSame(3, array_first($cart->getProductGiftCartItems())->getQuantity());
        self::assertSame(3, $facade->getRemovedGiftCartItemSetups($cart, 1)[0]->getQuantity());

        $facade->restoreProductGiftToCart($cart, $gift->getUuid(), 1, $first->getUuid());

        self::assertSame([3, 3], array_map(static fn (CartItem $item) => $item->getQuantity(), array_values($cart->getProductGiftCartItems())));
        self::assertFalse($cart->hasRemovedProductGifts());
    }

    public function testRemovalSurvivesZeroStockButIsPrunedWhenItsParentDisappears(): void
    {
        $first = $this->product(1);
        $second = $this->product(2);
        $gift = $this->product(3);
        $cart = $this->cart([$first, $second], [1, 1]);
        $cart->markProductGiftRemoved($gift, $first);
        $facade = $this->facade([1 => [$gift], 2 => [$gift]], 0);

        $facade->refreshProductGiftsInCart($cart, 1);
        self::assertSame([], $facade->getRemovedGiftCartItemSetups($cart, 1));
        self::assertTrue($cart->isProductGiftRemoved($gift, $first));
        $cart->retainRemovedProductGiftIds([2 => [3]]);

        self::assertFalse($cart->hasRemovedProductGifts());
        self::assertFalse($cart->isProductGiftRemoved($gift, $second));
    }

    public function testRepeatedReconciliationDoesNotDuplicateGiftRows(): void
    {
        $parent = $this->product(1);
        $gift = $this->product(3);
        $cart = $this->cart([$parent], [2]);
        $facade = $this->facade([1 => [$gift]]);

        $facade->refreshProductGiftsInCart($cart, 1);
        $originalGift = array_first($cart->getProductGiftCartItems());
        $facade->refreshProductGiftsInCart($cart, 1);
        $invalid = $facade->refreshProductGiftCartItemsAndGetInvalidItems($cart, 1);

        self::assertSame([], $invalid);
        self::assertSame([$originalGift], array_values($cart->getProductGiftCartItems()));
        self::assertSame(2, $originalGift->getQuantity());
    }

    private function product(int $id): Product
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
    private function cart(array $products, array $quantities): Cart
    {
        $identifier = $this->createStub(CustomerUserIdentifier::class);
        $identifier->method('getCartIdentifier')->willReturn('independent-gifts');
        $cart = (new CartFactory($this->entityNameResolver()))->create($identifier);

        foreach ($products as $i => $product) {
            $cart->addItem($this->itemFactory()->create($cart, $product, $quantities[$i], null, CartItemTypeEnum::TYPE_PRODUCT));
        }

        return $cart;
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\Product\Product[]> $giftsByParent
     */
    private function facade(array $giftsByParent, int $stock = 100): GiftCartFacade
    {
        $planFacade = $this->createStub(GiftPlanFacade::class);
        $planFacade->method('findActiveGiftProductsByMainProducts')->willReturn($giftsByParent);
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
        $factory = $this->createStub(CartItemFactory::class);
        $factory->method('create')->willReturnCallback(fn (Cart $cart, Product $product, int $quantity) => $this->itemFactory()->create($cart, $product, $quantity, null, CartItemTypeEnum::TYPE_PRODUCT_GIFT));

        return new GiftCartFacade($this->createStub(EntityManagerInterface::class), $factory, $planFacade, $setting, $currentUser, $visibilityFacade, $availabilityFacade);
    }

    private function entityNameResolver(): EntityNameResolver
    {
        $resolver = $this->createStub(EntityNameResolver::class);
        $resolver->method('resolve')->willReturnArgument(0);

        return $resolver;
    }

    private function itemFactory(): CartItemFactory
    {
        return new CartItemFactory($this->entityNameResolver(), new CartItemTypeEnum());
    }
}
