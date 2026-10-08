<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart\Watcher;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartWatcherTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductPriceCalculationForCustomerUser $productPriceCalculationForCustomerUser;

    /**
     * @inject
     */
    private CartWatcher $cartWatcher;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private GiftPlanSettingFacade $giftPlanSettingFacade;

    public function testGetModifiedPriceItemsAndUpdatePrices(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePricesForCurrentUser($product)->sellingProductPrice;
        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cartItem = new CartItem($cart, $product, 1, $productPrice->getPrice());
        $cart->addItem($cartItem);

        $modifiedItems1 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems1);

        $productData = $this->productDataFactory->createFromProduct($product);

        foreach ($productData->productInputPricesByDomain as $productInputPriceData) {
            foreach ($productInputPriceData->manualInputPricesByPricingGroupId as $pricingGroupId => $price) {
                $productInputPriceData->manualInputPricesByPricingGroupId[$pricingGroupId] = Money::create(10);
            }
        }

        $this->productFacade->edit($product->getId(), $productData);
        $this->resetRequestScopedCache();

        $modifiedItems2 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertNotEmpty($modifiedItems2);

        $modifiedItems3 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems3);
    }

    public function testGetNotListableItemsWithItemWithoutProduct(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cartItemStub = $this->createStub(CartItem::class);
        $cartItemStub->method('getProduct')->willThrowException(new ProductNotFoundException());

        $currentCustomerUserStub = $this->createCustomerUserStub();

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cart->addItem($cartItemStub);

        $notListableItems = $this->cartWatcher->getNotListableItems($cart, $currentCustomerUserStub);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotListableItemsWithVisibleButNotSellableProduct(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6', Product::class);
        $this->assertTrue($product->isCalculatedSellingDenied(Domain::FIRST_DOMAIN_ID));

        $cartItemStub = $this->createCartItemStub($product);

        $currentCustomerUserStub = $this->createCustomerUserStub();

        $productVisibilityFacadeStub = $this->createProductVisibilityFacadeStub();

        $cartWatcher = new CartWatcher(
            $this->productPriceCalculationForCustomerUser,
            $productVisibilityFacadeStub,
            $this->domain,
            $this->giftPlanSettingFacade,
        );

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cart->addItem($cartItemStub);

        $notListableItems = $cartWatcher->getNotListableItems($cart, $currentCustomerUserStub);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotVisibleItemsReturnsOnlyItemsWithNotVisibleProduct(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $visibleProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $notVisibleProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2', Product::class);

        $visibleProductCartItemStub = $this->createStub(CartItem::class);
        $visibleProductCartItemStub->method('hasProduct')->willReturn(true);
        $visibleProductCartItemStub->method('getProduct')->willReturn($visibleProduct);
        $notVisibleProductCartItemStub = $this->createStub(CartItem::class);
        $notVisibleProductCartItemStub->method('hasProduct')->willReturn(true);
        $notVisibleProductCartItemStub->method('getProduct')->willReturn($notVisibleProduct);
        $cartItemWithoutProductStub = $this->createStub(CartItem::class);
        $cartItemWithoutProductStub->method('hasProduct')->willReturn(false);

        $productVisibilityFacadeStub = $this->createStub(ProductVisibilityFacade::class);
        $productVisibilityFacadeStub
            ->method('getProductVisibility')
            ->willReturnCallback(function (Product $product) use ($notVisibleProduct): ProductVisibility {
                $productVisibilityStub = $this->createStub(ProductVisibility::class);
                $productVisibilityStub->method('isVisible')->willReturn($product !== $notVisibleProduct);

                return $productVisibilityStub;
            });

        $cartWatcher = new CartWatcher(
            $this->productPriceCalculationForCustomerUser,
            $productVisibilityFacadeStub,
            $this->domain,
            $this->giftPlanSettingFacade,
        );

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cart->addItem($visibleProductCartItemStub);
        $cart->addItem($notVisibleProductCartItemStub);
        $cart->addItem($cartItemWithoutProductStub);

        $notVisibleItems = $cartWatcher->getNotVisibleItems($cart, $this->createCustomerUserStub());
        $this->assertSame([$notVisibleProductCartItemStub], $notVisibleItems);
    }

    public function createCartItemStub(Product $product): CartItem
    {
        $cartItemStub = $this->createStub(CartItem::class);

        $cartItemStub
            ->method('getProduct')
            ->willReturn($product);

        return $cartItemStub;
    }

    public function createCustomerUserStub(): CurrentCustomerUser
    {
        $expectedPricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $currentCustomerUserStub = $this->createStub(CurrentCustomerUser::class);

        $currentCustomerUserStub
            ->method('getPricingGroup')
            ->willReturn($expectedPricingGroup);

        return $currentCustomerUserStub;
    }

    public function createProductVisibilityFacadeStub(): ProductVisibilityFacade
    {
        $productVisibilityStub = $this->createStub(ProductVisibility::class);

        $productVisibilityStub
            ->method('isVisible')
            ->willReturn(true);

        $productVisibilityFacadeStub = $this->createStub(ProductVisibilityFacade::class);

        $productVisibilityFacadeStub
            ->method('getProductVisibility')
            ->willReturn($productVisibilityStub);

        return $productVisibilityFacadeStub;
    }
}
