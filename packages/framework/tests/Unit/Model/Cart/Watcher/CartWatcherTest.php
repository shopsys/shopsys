<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Cart\Watcher;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPrice;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPricesResult;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;

class CartWatcherTest extends TestCase
{
    public function testUnchangedPriceLeavesCartItemUntouched(): void
    {
        $cartItemMock = $this->createCartItemMock(Money::create(121));
        $cartItemMock->expects($this->never())->method('setWatchedPrice');
        $cartWatcher = $this->createCartWatcher(Money::create(121));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([], $modifiedItems);
    }

    public function testChangedPriceIsWrittenToCartItemAndReported(): void
    {
        $cartItemMock = $this->createCartItemMock(Money::create(121));
        $cartItemMock->expects($this->once())->method('setWatchedPrice')->with(Money::create(242));
        $cartWatcher = $this->createCartWatcher(Money::create(242));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([$cartItemMock], $modifiedItems);
    }

    private function createCartWatcher(Money $currentPriceWithVat): CartWatcher
    {
        $productPrice = new ProductPrice(new Price(Money::create(100), $currentPriceWithVat), $this->createStub(PricingGroup::class), false);
        $productPriceCalculationStub = $this->createStub(ProductPriceCalculationForCustomerUser::class);
        $productPriceCalculationStub->method('calculatePricesForCurrentUser')->willReturn(new ProductPricesResult($productPrice, $productPrice));

        $giftPlanSettingFacadeStub = $this->createStub(GiftPlanSettingFacade::class);
        $giftPlanSettingFacadeStub->method('getInputGiftPrice')->willReturn(Money::zero());

        return new CartWatcher(
            $productPriceCalculationStub,
            $this->createStub(ProductVisibilityFacade::class),
            $this->createStub(Domain::class),
            $giftPlanSettingFacadeStub,
        );
    }

    private function createCartItemMock(Money $watchedPrice): CartItem&MockObject
    {
        $cartItemMock = $this->createMock(CartItem::class);
        $cartItemMock->method('getProduct')->willReturn($this->createStub(Product::class));
        $cartItemMock->method('getWatchedPrice')->willReturn($watchedPrice);

        return $cartItemMock;
    }

    private function createCart(CartItem $cartItem): Cart
    {
        $cartStub = $this->createStub(Cart::class);
        $cartStub->method('getProductCartItems')->willReturn([$cartItem]);
        $cartStub->method('getProductGiftCartItems')->willReturn([]);

        return $cartStub;
    }
}
