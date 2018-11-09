<?php

namespace Tests\ShopBundle\Functional\Model\Cart\Watcher;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\DataFixtures\Demo\PricingGroupDataFixture;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService;
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser;
use Shopsys\FrameworkBundle\Model\Product\ProductCategoryDomainFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Shopsys\ShopBundle\Model\Product\Product;
use Tests\ShopBundle\Test\FunctionalTestCase;

class CartWatcherServiceTest extends FunctionalTestCase
{
    public function testGetModifiedPriceItemsAndUpdatePrices()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        $vat = new Vat($vatData);
        $productData1 = $productDataFactory->create();
        $productData1->name = [];
        $productData1->vat = $vat;
        $productMock = Product::create($productData1);

        /** @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser $productPriceCalculationForUser */
        $productPriceCalculationForUser = $this->getContainer()->get(ProductPriceCalculationForUser::class);
        $productPrice = $productPriceCalculationForUser->calculatePriceForCurrentUser($productMock);
        $cartItem = new CartItem($customerIdentifier, $productMock, 1, $productPrice->getPriceWithVat());
        $cartItems = [$cartItem];
        $cart = new Cart($cartItems);

        /** @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService $cartWatcherService */
        $cartWatcherService = $this->getContainer()->get(CartWatcherService::class);

        $modifiedItems1 = $cartWatcherService->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems1);

        $productData2 = $productDataFactory->create();
        $productData2->name = [];
        $productData2->vat = $vat;

        $productMock->edit(new ProductCategoryDomainFactory(), $productData2);
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

        /** @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherService $cartWatcherService */
        $cartWatcherService = $this->getContainer()->get(CartWatcherService::class);

        $notListableItems = $cartWatcherService->getNotListableItems($cart, $currentCustomerMock);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotListableItemsWithVisibleButNotSellableProduct()
    {
        $productDataFactory = $this->getContainer()->get(ProductDataFactoryInterface::class);

        $productData = $productDataFactory->create();
        $productData->name = [];
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = 21;
        $productData->vat = new Vat($vatData);
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

        $productPriceCalculationForUser = $this->getContainer()->get(ProductPriceCalculationForUser::class);
        $domain = $this->getContainer()->get(Domain::class);

        $cartWatcherService = new CartWatcherService($productPriceCalculationForUser, $productVisibilityRepositoryMock, $domain);

        $cartItems = [$cartItemMock];
        $cart = new Cart($cartItems);

        $notListableItems = $cartWatcherService->getNotListableItems($cart, $currentCustomerMock);
        $this->assertCount(1, $notListableItems);
    }
}
