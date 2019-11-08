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
use Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibility;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartWatcherTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     * @inject
     */
    private $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcher
     * @inject
     */
    private $cartWatcher;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceFacade
     * @inject
     */
    private $manualInputPriceFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductDataFactoryInterface
     * @inject
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     * @inject
     */
    private $vatFacade;

    public function testGetModifiedPriceItemsAndUpdatePrices()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');

        $productPrice = $this->productPriceCalculationForUser->calculatePriceForCurrentUser($product);
        $cart = new Cart($customerIdentifier->getCartIdentifier());
        $cartItem = new CartItem($cart, $product, 1, $productPrice->getPriceWithVat());
        $cart->addItem($cartItem);

        $modifiedItems1 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems1);

        $pricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);

        $this->manualInputPriceFacade->refresh($product, $pricingGroup, Money::create(10));

        $modifiedItems2 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertNotEmpty($modifiedItems2);

        $modifiedItems3 = $this->cartWatcher->getModifiedPriceItemsAndUpdatePrices($cart);
        $this->assertEmpty($modifiedItems3);
    }

    public function testGetNotListableItemsWithItemWithoutProduct()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->setMethods(null)
            ->getMock();

        $expectedPricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
        $currentCustomerMock = $this->getMockBuilder(CurrentCustomer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPricingGroup'])
            ->getMock();
        $currentCustomerMock
            ->expects($this->any())
            ->method('getPricingGroup')
            ->willReturn($expectedPricingGroup);

        $cart = new Cart($customerIdentifier->getCartIdentifier());
        $cart->addItem($cartItemMock);

        $notListableItems = $this->cartWatcher->getNotListableItems($cart, $currentCustomerMock);
        $this->assertCount(1, $notListableItems);
    }

    public function testGetNotListableItemsWithVisibleButNotSellableProduct()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $this->setVats($productData);
        $product = Product::create($productData);

        $cartItemMock = $this->getMockBuilder(CartItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['getProduct'])
            ->getMock();
        $cartItemMock
            ->expects($this->any())
            ->method('getProduct')
            ->willReturn($product);

        $expectedPricingGroup = $this->getReferenceForDomain(PricingGroupDataFixture::PRICING_GROUP_ORDINARY, Domain::FIRST_DOMAIN_ID);
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

        $cartWatcher = new CartWatcher($this->productPriceCalculationForUser, $productVisibilityRepositoryMock, $this->domain);

        $cart = new Cart($customerIdentifier->getCartIdentifier());
        $cart->addItem($cartItemMock);

        $notListableItems = $cartWatcher->getNotListableItems($cart, $currentCustomerMock);
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
}
