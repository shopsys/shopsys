<?php

namespace Tests\FrameworkBundle\Unit\Model\Cart;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Cart\Item\CartItem;
use Shopsys\FrameworkBundle\Model\Customer\CustomerIdentifier;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class CartTest extends TestCase
{
    public function testGetItemsCountZero()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');
        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $this->assertSame(0, $cart->getItemsCount());
    }

    public function testGetItemsCount()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');

        $productData1 = new ProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = new ProductData();
        $productData2->name = ['cs' => 'Product 2'];
        $product2 = Product::create($productData2);

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);

        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $this->assertSame(2, $cart->getItemsCount());
    }

    public function testIsEmpty()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $this->assertTrue($cart->isEmpty());
    }

    public function testIsNotEmpty()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');
        $productData = new ProductData();
        $productData->name = ['cs' => 'Product 1'];
        $product = Product::create($productData);

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem = new CartItem($cart, $product, 1, Money::zero());
        $cart->addItem($cartItem);

        $this->assertFalse($cart->isEmpty());
    }

    public function testClean()
    {
        $customerIdentifier = new CustomerIdentifier('randomString');
        $productData1 = new ProductData();
        $productData1->name = ['cs' => 'Product 1'];
        $product1 = Product::create($productData1);

        $productData2 = new ProductData();
        $productData2->name = ['cs' => 'Product 2'];

        $product2 = Product::create($productData2);

        $cart = new Cart($customerIdentifier->getCartIdentifier());

        $cartItem1 = new CartItem($cart, $product1, 1, Money::zero());
        $cart->addItem($cartItem1);
        $cartItem2 = new CartItem($cart, $product2, 3, Money::zero());
        $cart->addItem($cartItem2);

        $cart->clean();

        $this->assertTrue($cart->isEmpty());
    }
}
