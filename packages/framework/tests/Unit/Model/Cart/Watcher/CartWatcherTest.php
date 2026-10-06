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
        $cartItemMock = $this->createCartItemMock(Money::create(121), Money::create(100));
        $cartItemMock->expects($this->never())->method('setWatchedPrice');
        $cartWatcher = $this->createCartWatcher(Money::create(121), Money::create(100));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([], $modifiedItems);
    }

    public function testChangedPriceIsWrittenToCartItemAndReported(): void
    {
        $cartItemMock = $this->createCartItemMock(Money::create(121), Money::create(100));
        $cartItemMock->expects($this->once())->method('setWatchedPrice')->with(new Price(Money::create(200), Money::create(242)));
        $cartWatcher = $this->createCartWatcher(Money::create(242), Money::create(200));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([$cartItemMock], $modifiedItems);
    }

    public function testChangedPriceWithoutVatIsWrittenToCartItemAndReported(): void
    {
        $cartItemMock = $this->createCartItemMock(Money::create(121), Money::create(100));
        $cartItemMock->expects($this->once())->method('setWatchedPrice')->with(new Price(Money::create(110), Money::create(121)));
        $cartWatcher = $this->createCartWatcher(Money::create(121), Money::create(110));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([$cartItemMock], $modifiedItems);
    }

    public function testMissingWatchedPriceIsTakenOverWithoutBeingReported(): void
    {
        $cartItemMock = $this->createCartItemMock(null, null);
        $cartItemMock->expects($this->once())->method('setWatchedPrice')->with(new Price(Money::create(100), Money::create(121)));
        $cartWatcher = $this->createCartWatcher(Money::create(121), Money::create(100));

        $modifiedItems = $cartWatcher->getModifiedPriceItemsAndUpdatePrices($this->createCart($cartItemMock));

        $this->assertSame([], $modifiedItems);
    }

    private function createCartWatcher(Money $currentPriceWithVat, Money $currentPriceWithoutVat): CartWatcher
    {
        $productPrice = new ProductPrice(new Price($currentPriceWithoutVat, $currentPriceWithVat), $this->createStub(PricingGroup::class), false);
        $productPriceCalculationStub = $this->createStub(ProductPriceCalculationForCustomerUser::class);
        $productPriceCalculationStub->method('calculatePricesForCurrentUser')->willReturn(new ProductPricesResult($productPrice, $productPrice));

        return new CartWatcher(
            $productPriceCalculationStub,
            $this->createStub(ProductVisibilityFacade::class),
            $this->createStub(Domain::class),
            $this->createStub(GiftPlanSettingFacade::class),
        );
    }

    private function createCartItemMock(
        ?Money $watchedPriceWithVat,
        ?Money $watchedPriceWithoutVat,
    ): CartItem&MockObject {
        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct', 'setWatchedPrice'])
            ->getMock();
        $cartItemMock->method('getProduct')->willReturn($this->createStub(Product::class));

        // the stored watched price is set directly, because setWatchedPrice() is mocked
        (function () use ($watchedPriceWithVat, $watchedPriceWithoutVat): void {
            $this->watchedPriceWithVat = $watchedPriceWithVat;
            $this->watchedPriceWithoutVat = $watchedPriceWithoutVat;
        })->call($cartItemMock);

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
