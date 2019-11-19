<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\CartFacade;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifierFactory;
use Tests\App\Test\TransactionFunctionalTestCase;

class CartFacadeTest extends TransactionFunctionalTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Localization\TranslatableListener
     * @inject
     */
    private $translatableListener;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFactory
     * @inject
     */
    private $cartFactory;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\ProductRepository
     * @inject
     */
    private $productRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\CurrentCustomer
     * @inject
     */
    private $currentCustomer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade
     * @inject
     */
    private $currentPromoCodeFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculationForUser
     * @inject
     */
    private $productPriceCalculationForUser;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     * @inject
     */
    private $cartItemFactoryInterface;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartRepository
     * @inject
     */
    private $cartRepository;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Watcher\CartWatcherFacade
     * @inject
     */
    private $cartWatcherFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     * @inject
     */
    private $cartFacadeFromContainer;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Cart\Item\CartItemFactoryInterface
     * @inject
     */
    private $cartItemFactory;

    public function testAddProductToCartAddsItemsOnlyToCurrentCart()
    {
        $customerIdentifier = new CustomerIdentifier('secretSessionHash');
        $anotherCustomerIdentifier = new CustomerIdentifier('anotherSecretSessionHash');

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $productId = $product->getId();
        $quantity = 10;

        $cartFacade = $this->createCartFacade($customerIdentifier);

        $cartFacade->addProductToCart($productId, $quantity);

        $cart = $this->getCartByCustomerIdentifier($customerIdentifier);
        $cartItems = $cart->getItems();
        $product = array_pop($cartItems)->getProduct();
        $this->assertSame($productId, $product->getId(), 'Add correct product');

        $anotherCart = $this->getCartByCustomerIdentifier($anotherCustomerIdentifier);
        $this->assertSame(0, $anotherCart->getItemsCount(), 'Add only in their own cart');
    }

    public function testCannotAddUnsellableProductToCart()
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '6');
        $productId = $product->getId();
        $quantity = 1;

        $customerIdentifier = new CustomerIdentifier('secretSessionHash');
        $cartFacade = $this->createCartFacade($customerIdentifier);

        $this->expectException('\Shopsys\FrameworkBundle\Model\Product\Exception\ProductNotFoundException');
        $cartFacade->addProductToCart($productId, $quantity);

        $cart = $this->getCartByCustomerIdentifier($customerIdentifier);
        $cartItems = $cart->getItems();

        $this->assertEmpty($cartItems, 'Product add not suppressed');
    }

    public function testCanChangeCartItemsQuantities()
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '3');

        $customerIdentifier = new CustomerIdentifier('secretSessionHash');
        $cartFacade = $this->createCartFacade($customerIdentifier);

        $cartItem1 = $cartFacade->addProductToCart($product1->getId(), 1)->getCartItem();
        $cartItem2 = $cartFacade->addProductToCart($product2->getId(), 2)->getCartItem();

        $cartFacade->changeQuantities([
            $cartItem1->getId() => 5,
            $cartItem2->getId() => 9,
        ]);

        $cart = $this->getCartByCustomerIdentifier($customerIdentifier);
        foreach ($cart->getItems() as $cartItem) {
            if ($cartItem->getId() === $cartItem1->getId()) {
                $this->assertSame(5, $cartItem->getQuantity(), 'Correct change quantity product');
            } elseif ($cartItem->getId() === $cartItem2->getId()) {
                $this->assertSame(9, $cartItem->getQuantity(), 'Correct change quantity product');
            } else {
                $this->fail('Unexpected product in cart');
            }
        }
    }

    public function testCannotDeleteNonexistentCartItem()
    {
        $customerIdentifier = new CustomerIdentifier('secretSessionHash');

        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        $quantity = 1;

        $cartFacade = $this->createCartFacade($customerIdentifier);
        $cartFacade->addProductToCart($product->getId(), $quantity);

        $cart = $this->getCartByCustomerIdentifier($customerIdentifier);
        $cartItems = $cart->getItems();
        $cartItem = array_pop($cartItems);

        $this->expectException('\Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidCartItemException');
        $cartFacade->deleteCartItem($cartItem->getId() + 1);
    }

    public function testCanDeleteCartItem()
    {
        // Set currentLocale in TranslatableListener as it done in real request
        // because CartWatcherFacade works with entity translations.
        $this->translatableListener->setCurrentLocale('cs');

        $customerIdentifier = new CustomerIdentifier('secretSessionHash');

        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1');
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '2');
        $quantity = 1;

        $cartFacade = $this->createCartFacade($customerIdentifier);
        $cartItem1 = $cartFacade->addProductToCart($product1->getId(), $quantity)->getCartItem();
        $cartItem2 = $cartFacade->addProductToCart($product2->getId(), $quantity)->getCartItem();

        $cartFacade->deleteCartItem($cartItem1->getId());

        $cart = $this->getCartByCustomerIdentifier($customerIdentifier);
        $cartItems = $cart->getItems();

        $this->assertArrayHasSameElements([$cartItem2], $cartItems);
    }

    /**
     * @dataProvider productCartDataProvider
     * @param int $productId
     * @param bool $cartShouldBeNull
     */
    public function testCartNotExistIfNoListableProductIsInCart(int $productId, bool $cartShouldBeNull): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId);

        $cart = $this->cartFacadeFromContainer->getCartOfCurrentCustomerCreateIfNotExists();
        $cartItem = $this->cartItemFactory->create($cart, $product, 1, Money::create(10));
        $cart->addItem($cartItem);

        $this->getEntityManager()->persist($cartItem);
        $this->getEntityManager()->flush();

        $this->assertFalse($cart->isEmpty(), 'Cart should not be empty');

        $cart = $this->cartFacadeFromContainer->findCartOfCurrentCustomer();

        if ($cartShouldBeNull) {
            $this->assertNull($cart);
        } else {
            $this->assertEquals(1, $cart->getItemsCount());
        }
    }

    public function productCartDataProvider()
    {
        return [
            ['productId' => 1, 'cartShouldBeNull' => false],
            ['productId' => 34, 'cartShouldBeNull' => true], // not listable product

        ];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\CartFacade
     */
    private function createCartFacade(CustomerIdentifier $customerIdentifier)
    {
        return new CartFacade(
            $this->getEntityManager(),
            $this->cartFactory,
            $this->productRepository,
            $this->getCustomerIdentifierFactoryMock($customerIdentifier),
            $this->domain,
            $this->currentCustomer,
            $this->currentPromoCodeFacade,
            $this->productPriceCalculationForUser,
            $this->cartItemFactoryInterface,
            $this->cartRepository,
            $this->cartWatcherFacade
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @return \Shopsys\FrameworkBundle\Model\Cart\Cart
     */
    private function getCartByCustomerIdentifier(CustomerIdentifier $customerIdentifier)
    {
        return $this->cartFacadeFromContainer->getCartByCustomerIdentifierCreateIfNotExists($customerIdentifier);
    }

    /**
     * @param array $expected
     * @param array $actual
     */
    private function assertArrayHasSameElements(array $expected, array $actual)
    {
        foreach ($expected as $expectedElement) {
            $key = array_search($expectedElement, $actual, true);

            if ($key === false) {
                $this->fail('Actual array does not contain expected element: ' . var_export($expectedElement, true));
            }

            unset($actual[$key]);
        }

        if (!empty($actual)) {
            $this->fail('Actual array contains extra elements: ' . var_export($actual, true));
        }
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier $customerIdentifier
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function getCustomerIdentifierFactoryMock(CustomerIdentifier $customerIdentifier)
    {
        $customerIdentifierFactoryMock = $this->getMockBuilder(CustomerIdentifierFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $customerIdentifierFactoryMock->method('get')->willReturn($customerIdentifier);

        return $customerIdentifierFactoryMock;
    }

    /**
     * @return \App\Model\Product\Product
     */
    private function createProduct()
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testCannotAddProductFloatQuantityToCart()
    {
        $product = $this->createProduct();

        $this->expectException('Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException');
        $this->cartFacadeFromContainer->addProductToCart($product->getId(), 1.1);
    }

    public function testCannotAddProductZeroQuantityToCart()
    {
        $product = $this->createProduct();

        $this->expectException('Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException');
        $this->cartFacadeFromContainer->addProductToCart($product->getId(), 0);
    }

    public function testCannotAddProductNegativeQuantityToCart()
    {
        $product = $this->createProduct();

        $this->expectException('Shopsys\FrameworkBundle\Model\Cart\Exception\InvalidQuantityException');
        $this->cartFacadeFromContainer->addProductToCart($product->getId(), -10);
    }

    public function testAddProductToCartMarksAddedProductAsNew()
    {
        $product = $this->createProduct();

        $result = $this->cartFacadeFromContainer->addProductToCart($product->getId(), 2);
        $this->assertTrue($result->getIsNew());
    }

    public function testAddProductToCartMarksRepeatedlyAddedProductAsNotNew()
    {
        $product = $this->createProduct();

        $this->cartFacadeFromContainer->addProductToCart($product->getId(), 1);
        $result = $this->cartFacadeFromContainer->addProductToCart($product->getId(), 2);
        $this->assertFalse($result->getIsNew());
    }

    public function testAddProductResultContainsAddedProductQuantity()
    {
        $product = $this->createProduct();

        $quantity = 2;
        $result = $this->cartFacadeFromContainer->addProductToCart($product->getId(), $quantity);
        $this->assertSame($quantity, $result->getAddedQuantity());
    }

    public function testAddProductResultDoesNotContainPreviouslyAddedProductQuantity()
    {
        $product = $this->createProduct();

        $cartFacade = $this->cartFacadeFromContainer;
        $cartFacade->addProductToCart($product->getId(), 1);
        $quantity = 2;

        $result = $cartFacade->addProductToCart($product->getId(), $quantity);
        $this->assertSame($quantity, $result->getAddedQuantity());
    }
}
