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
use Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanSettingFacade;
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
        $cartItem = new CartItem($cart, $product, 1, $productPrice->getPrice()->getPriceWithVat());
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

        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->create();
        $productData->name = [];
        $this->setVatsAndPrices($productData);

        $cartItemStub = $this->createCartItemStub($productData);

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

    private function setVatsAndPrices(ProductData $productData): void
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $productData->productInputPricesByDomain[$domainId] = $this->productInputPriceDataFactory->create(
                $this->vatFacade->getDefaultVatForDomain($domainId),
                [1 => Money::zero(), 2 => Money::zero()],
            );
        }
    }

    public function createCartItemStub(ProductData $productData): CartItem
    {
        $product = Product::create($productData);

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
