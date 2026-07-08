<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Cart;

use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class CartTest extends TestCase
{
    public function testGetItemsCountZero(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $this->assertSame(0, $cart->getItemsCount());
    }

    public function testGetItemsCount(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $productData1 = TestProductProvider::getTestProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = TestProductProvider::getTestProductData();
        $productData2->name = ['cs' => 'Product 2'];
        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);

        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $this->assertSame(2, $cart->getItemsCount());
    }

    public function testIsEmpty(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $this->assertTrue($cart->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $productData = TestProductProvider::getTestProductData();
        $productData->name = ['cs' => 'Product 1'];
        $product = Product::create($productData);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $this->assertFalse($cart->isEmpty());
    }

    public function testGetPersonalPickupOnlyProducts(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $personalPickupOnlyProductData = TestProductProvider::getTestProductData();
        $personalPickupOnlyProductData->name = ['cs' => 'Personal pickup only product'];
        $personalPickupOnlyProductData->personalPickupOnly = true;
        $personalPickupOnlyProduct = Product::create($personalPickupOnlyProductData);

        $commonProductData = TestProductProvider::getTestProductData();
        $commonProductData->name = ['cs' => 'Common product'];
        $commonProduct = Product::create($commonProductData);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cart->addItem($this->createCartItemWithId($cart, $personalPickupOnlyProduct, 1));
        $cart->addItem($this->createCartItemWithId($cart, $commonProduct, 2));

        $personalPickupOnlyProducts = $cart->getPersonalPickupOnlyProducts();

        $this->assertCount(1, $personalPickupOnlyProducts);
        $this->assertContains($personalPickupOnlyProduct, $personalPickupOnlyProducts);
        $this->assertNotContains($commonProduct, $personalPickupOnlyProducts);
    }

    private function createCartItemWithId(Cart $cart, Product $product, int $id): CartItem
    {
        $cartItem = new CartItem($cart, $product, 1, Money::zero());

        $idReflectionProperty = new ReflectionProperty(CartItem::class, 'id');
        $idReflectionProperty->setValue($cartItem, $id);

        return $cartItem;
    }

    public function testGetPersonalPickupOnlyProductsReturnsEmptyArrayWhenNoneFlagged(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $productData = TestProductProvider::getTestProductData();
        $productData->name = ['cs' => 'Common product'];
        $product = Product::create($productData);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);
        $cart->addItem($this->createCartItemWithId($cart, $product, 1));

        $this->assertSame([], $cart->getPersonalPickupOnlyProducts());
    }

    public function testClean(): void
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $productData1 = TestProductProvider::getTestProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = TestProductProvider::getTestProductData();
        $productData2->name = ['cs' => 'Product 2'];

        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier(), null);

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }
}
