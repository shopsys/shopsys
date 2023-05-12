<?php

namespace Tests\FrameworkBundle\Unit\Model\Cart;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class CartTest extends TestCase
{
    public function testGetItemsCountZero()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $this->assertSame(0, $cart->getItemsCount());
    }

    public function testGetItemsCount()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $productData1 = TestProductProvider::getTestProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = TestProductProvider::getTestProductData();
        $productData2->name = ['cs' => 'Product 2'];
        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);

        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $this->assertSame(2, $cart->getItemsCount());
    }

    public function testIsEmpty()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $this->assertTrue($cart->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $productData = TestProductProvider::getTestProductData();
        $productData->name = ['cs' => 'Product 1'];
        $product = Product::create($productData);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $this->assertFalse($cart->isEmpty());
    }

    public function testClean()
    {
        $customerUserIdentifier = new CustomerUserIdentifier('randomString');
        $productData1 = TestProductProvider::getTestProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = TestProductProvider::getTestProductData();
        $productData2->name = ['cs' => 'Product 2'];

        $product2 = Product::create($productData2);

        $cart = new Cart($customerUserIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }
}
