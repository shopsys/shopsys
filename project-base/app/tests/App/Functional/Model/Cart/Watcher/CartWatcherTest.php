<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart\Watcher;

use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use PHPUnit\Framework\MockObject\MockObject;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher;
use Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupRepository;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForCustomerUser;
use Shopsys\FrameworkBundle\Model\Product\ProductDataFactory;
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
    private ProductManualInputPriceFacade $manualInputPriceFacade;

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
    private PricingGroupRepository $pricingGroupRepository;

    public function testGetModifiedPriceItemsAndUpdatePrices()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $productPrice = $this->productPriceCalculationForCustomerUser->calculatePriceForCurrentUser($product);
        $cart = new Cart($customerUserIdentifier->getCartIdentifier());
        $cartItem = new CartItem($cart, $product, 1, $productPrice->getPriceWithVat());
        $cart->addItem($cartItem);

        $modifiedItems1 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems1);

        $pricesByPricingGroupId = [];

        foreach ($this->pricingGroupRepository->getAll() as $pricingGroup) {
            $pricesByPricingGroupId[$pricingGroup->getId()] = Money::create(10);
        }

        $this->manualInputPriceFacade->refreshProductManualInputPrices($product, $pricesByPricingGroupId);

        $modifiedItems2 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertNotEmpty($modifiedItems2);

        $modifiedItems3 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems3);
    }

    public function testGetNotListableItemsWithItemWithoutProduct()
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

    public function testGetNotListableItemsWithVisibleButNotSellableProduct()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $productData->manualInputPricesByPricingGroupId = [1 => Money::zero(), 2 => Money::zero()];
        $this->setVats($productData);

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
    private function setVats(ProductData $productData): void
    {
        $productVatsIndexedByDomainId = [];

        foreach ($this->domain->getAllIds() as $domainId) {
            $productVatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }
        $productData->vatsIndexedByDomainId = $productVatsIndexedByDomainId;
    }

    /**
     * @param \App\Model\Product\ProductData $productData
     * @return \PHPUnit\Framework\MockObject\MockObject|(\Shopsys\FrameworkBundle\Model\Cart\Item\CartItem&\PHPUnit\Framework\MockObject\MockObject)
     */
    public function createCartItemMock(ProductData $productData): MockObject|CartItem
    {
        $product = Product::create($productData);

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProduct'])
            ->getMock();

        $cartItemMock
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        return $cartItemMock;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|(\Shopsys\FrameworkBundle\Model\Customer\User\CurrentCustomerUser&\PHPUnit\Framework\MockObject\MockObject)
     */
    public function createCustomerUserMock(): CurrentCustomerUser|MockObject
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
            ->expects($this->any())
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
            ->expects($this->any())
            ->method('isVisible')
            ->willReturn(true);

        $productVisibilityFacadeMock = $this->getMockBuilder(ProductVisibilityFacade::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProductVisibility'])
            ->getMock();

        $productVisibilityFacadeMock
            ->expects($this->any())
            ->method('getProductVisibility')
            ->willReturn($productVisibilityMock);

        return $productVisibilityFacadeMock;
    }
}
