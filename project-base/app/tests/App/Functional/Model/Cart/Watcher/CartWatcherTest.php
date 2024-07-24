<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart\Watcher;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductInputPriceDataFactory;
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
    private VatFacade $vatFacade;

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductInputPriceDataFactory $productInputPriceDataFactory;

    public function testGetModifiedPriceItemsAndUpdatePrices(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser($product);
        $cart = new Cart($customerUserIdentifier->getCartIdentifier());
        $cartItem = new CartItem($cart, $product, 1, $productPrice->getPriceWithVat());
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

        $modifiedItems2 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertNotEmpty($modifiedItems2);

        $modifiedItems3 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems3);
    }

    public function testGetNotListableItemsWithItemWithoutProduct(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $currentCustomerUserMock = $this->createCustomerUserMock();

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());
        $cart->addItem($cartItemMock);

        $notListableItems = $this->cartWatcher->getNotListableItems($cart, $currentCustomerUserMock);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotListableItemsWithVisibleButNotSellableProduct(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $this->setVatsAndPrices($productData);

        $cartItemMock = $this->createCartItemMock($productData);

        $currentCustomerUserMock = $this->createCustomerUserMock();

        $productVisibilityFacadeMock = $this->createProductVisibilityFacadeMock();

        $cartWatcher = new CartWatcher(
            $this->productPriceCalculationForCustomerUser,
            $productVisibilityFacadeMock,
            $this->domain,
        );

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());
        $cart->addItem($cartItemMock);

        $notListableItems = $cartWatcher->getNotListableItems($cart, $currentCustomerUserMock);
        $this->assertCount(1, $notListableItems);
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     */
    private function setVatsAndPrices(ProductData $productData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->productInputPricesByDomain[$domainId] = $this->productInputPriceDataFactory->create(
                $this->vatFacade->getDefaultVatForDomain($domainId),
                [1 => Money::zero(), 2 => Money::zero()],
            );
        }
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \Shopsys\FrameworkBundle\Model\Cart\Item\CartItem
     */
    public function createCartItemMock(ProductData $productData): CartItem
    {
        $product = Product::create($productData);

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->getMock();

        $cartItemMock
            ->method('getProduct')
            ->willReturn($product);

        return $cartItemMock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser
     */
    public function createCustomerUserMock(): CurrentCustomerUser
    {
        $expectedPricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $currentCustomerUserMock = $this->getMockBuilder(CurrentCustomerUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getPricingGroup'])
            ->getMock();

        $currentCustomerUserMock
            ->method('getPricingGroup')
            ->willReturn($expectedPricingGroup);

        return $currentCustomerUserMock;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade
     */
    public function createProductVisibilityFacadeMock(): ProductVisibilityFacade
    {
        $productVisibilityMock = $this->getMockBuilder(ProductVisibility::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isVisible'])
            ->getMock();

        $productVisibilityMock
            ->method('isVisible')
            ->willReturn(true);

        $productVisibilityFacadeMock = $this->getMockBuilder(ProductVisibilityFacade::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductVisibility'])
            ->getMock();

        $productVisibilityFacadeMock
            ->method('getProductVisibility')
            ->willReturn($productVisibilityMock);

        return $productVisibilityFacadeMock;
    }
}
