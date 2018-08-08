<?php

namespace Tests\FrameworkBundle\Unit\Model\Order\Item;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProduct;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderProductService;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductData;

class OrderProductServiceTest extends TestCase
{
    public function testSubtractOrderProductsFromStockUsingStock(): void
    {
        $productStockQuantity = 15;
        $orderProductQuantity = 10;

        $orderMock = $this->createMock(Order::class);

        $productData = new ProductData();
        $productData->usingStock = true;
        $productData->stockQuantity = $productStockQuantity;
        $product = Product::create($productData);
        $productPrice = new Price(0, 0);

        $orderProduct = new OrderProduct($orderMock, 'productName', $productPrice, 0, $orderProductQuantity, null, null, $product);

        $orderProductService = new OrderProductService();
        $orderProductService->subtractOrderProductsFromStock([$orderProduct]);

        $this->assertSame($productStockQuantity - $orderProductQuantity, $product->getStockQuantity());
    }

    public function testSubtractOrderProductsFromStockNotUsingStock(): void
    {
        $productStockQuantity = 15;
        $orderProductQuantity = 10;

        $orderMock = $this->createMock(Order::class);

        $productData = new ProductData();
        $productData->usingStock = false;
        $productData->stockQuantity = $productStockQuantity;
        $product = Product::create($productData);
        $productPrice = new Price(0, 0);

        $orderProduct = new OrderProduct($orderMock, 'productName', $productPrice, 0, $orderProductQuantity, null, null, $product);

        $orderProductService = new OrderProductService();
        $orderProductService->subtractOrderProductsFromStock([$orderProduct]);

        $this->assertSame($productStockQuantity, $product->getStockQuantity());
    }

    public function testAddOrderProductsToStockUsingStock(): void
    {
        $productStockQuantity = 15;
        $orderProductQuantity = 10;

        $orderMock = $this->createMock(Order::class);

        $productData = new ProductData();
        $productData->usingStock = true;
        $productData->stockQuantity = $productStockQuantity;
        $product = Product::create($productData);
        $productPrice = new Price(0, 0);

        $orderProduct = new OrderProduct($orderMock, 'productName', $productPrice, 0, $orderProductQuantity, null, null, $product);

        $orderProductService = new OrderProductService();
        $orderProductService->returnOrderProductsToStock([$orderProduct]);

        $this->assertSame($productStockQuantity + $orderProductQuantity, $product->getStockQuantity());
    }

    public function testAddOrderProductsToStockNotUsingStock(): void
    {
        $productStockQuantity = 15;
        $orderProductQuantity = 10;

        $orderMock = $this->createMock(Order::class);

        $productData = new ProductData();
        $productData->usingStock = false;
        $productData->stockQuantity = $productStockQuantity;
        $product = Product::create($productData);
        $productPrice = new Price(0, 0);

        $orderProduct = new OrderProduct($orderMock, 'productName', $productPrice, 0, $orderProductQuantity, null, null, $product);

        $orderProductService = new OrderProductService();
        $orderProductService->returnOrderProductsToStock([$orderProduct]);

        $this->assertSame($productStockQuantity, $product->getStockQuantity());
    }
}
