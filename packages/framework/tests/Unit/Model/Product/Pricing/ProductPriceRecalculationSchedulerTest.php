<?php

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceRecalculationScheduler;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;

class ProductPriceRecalculationSchedulerTest extends TestCase
{
    public function testAllProductsCanBeScheduledForDelayedRecalculation()
    {
        $productMock = $this->getMockBuilder(Product::class)
            ->setMethods(null)
            ->disableOriginalConstructor()
            ->getMock();
        $productsIterator = [$productMock];
        $productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->setMethods(['markAllProductsForPriceRecalculation', 'getProductsForPriceRecalculationIterator'])
            ->disableOriginalConstructor()
            ->getMock();
        $productRepositoryMock->expects($this->once())->method('markAllProductsForPriceRecalculation');
        $productRepositoryMock
            ->expects($this->once())
            ->method('getProductsForPriceRecalculationIterator')
            ->willReturn($productsIterator);

        $productPriceRecalculationScheduler = new ProductPriceRecalculationScheduler($productRepositoryMock);
        $productPriceRecalculationScheduler->scheduleAllProductsForDelayedRecalculation();

        $this->assertSame($productsIterator, $productPriceRecalculationScheduler->getProductsIteratorForDelayedRecalculation());
    }
}
