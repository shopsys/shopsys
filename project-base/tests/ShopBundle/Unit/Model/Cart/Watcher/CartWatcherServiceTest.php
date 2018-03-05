<?php

namespace Tests\ShopBundle\Unit\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DataFixtures\Base\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Tests\ShopBundle\Test\FunctionalTestCase;

class CartWatcherServiceTest extends FunctionalTestCase
{
    public function testGetModifiedPriceItemsAndUpdatePrices()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');

        $vat = new Vat(new VatData('vat', 21));
        $productData1 = new ProductData();
        $productData1->name = [];
        $productData1->price = 100;
        $productData1->vat = $vat;
        $productData1->priceCalculationType = Product::PRICE_CALCULATION_TYPE_AUTO;
        $productMock = Product::create($productData1);

        $productPriceCalculationForUser = $this->getServiceByType(ProductPriceCalculationForUser::class);
        /* @var $productPriceCalculationForUser \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser */
        $productPrice = $productPriceCalculationForUser->calculatePriceForCurrentUser($productMock);
        $cartItem = new CartItem($customerIdentifier, $productMock, 1, $productPrice->getPriceWithVat());
        $cartItems = [$cartItem];
        $cart = new Cart($cartItems);

        $cartWatcherService = $this->getServiceByType(CartWatcherService::class);
        /* @var $cartWatcherService \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService */

        $modifiedItems1 = $cartWatcherService->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems1);

        $productData2 = new ProductData();
        $productData2->name = [];
        $productData2->price = 200;
        $productData2->vat = $vat;

        $productMock->edit($productData2);
        $modifiedItems2 = $cartWatcherService->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertNotEmpty($modifiedItems2);

        $modifiedItems3 = $cartWatcherService->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems3);
    }

    public function testGetNotListableItemsWithItemWithoutProduct()
    {
        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedPricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);
        $currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPricingGroup'])
            ->getMock();
        $currentCustomerMock
            ->expects($this->any())
            ->method('getPricingGroup')
            ->willReturn($expectedPricingGroup);

        $cartItems = [$cartItemMock];
        $cart = new Cart($cartItems);

        $cartWatcherService = $this->getServiceByType(CartWatcherService::class);
        /* @var $cartWatcherService \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService */

        $notListableItems = $cartWatcherService->getNotListableItems($cart, $currentCustomerMock);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotListableItemsWithVisibleButNotSellableProduct()
    {
        $productData = new ProductData();
        $productData->name = [];
        $productData->price = 100;
        $productData->vat = new Vat(new VatData('vat', 21));
        $product = Product::create($productData);

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $cartItemMock
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        $expectedPricingGroup = $this->getReference(PricingGroupDataFixture::PRICING_GROUP_ORDINARY_DOMAIN_1);
        $currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPricingGroup'])
            ->getMock();
        $currentCustomerMock
            ->expects($this->any())
            ->method('getPricingGroup')
            ->willReturn($expectedPricingGroup);

        $productVisibilityMock = $this->getMockBuilder(ProductVisibility::class)
            ->disableOriginalConstructor()
            ->setMethods(['isVisible'])
            ->getMock();
        $productVisibilityMock
            ->expects($this->any())
            ->method('isVisible')
            ->willReturn(true);

        $productVisibilityRepositoryMock = $this->getMockBuilder(ProductVisibilityRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProductVisibility'])
            ->getMock();
        $productVisibilityRepositoryMock
            ->expects($this->any())
            ->method('getProductVisibility')
            ->willReturn($productVisibilityMock);

        $productPriceCalculationForUser = $this->getServiceByType(ProductPriceCalculationForUser::class);
        $domain = $this->getServiceByType(Domain::class);

        $cartWatcherService = new CartWatcherService($productPriceCalculationForUser, $productVisibilityRepositoryMock, $domain);

        $cartItems = [$cartItemMock];
        $cart = new Cart($cartItems);

        $notListableItems = $cartWatcherService->getNotListableItems($cart, $currentCustomerMock);
        $this->assertCount(1, $notListableItems);
    }
}
