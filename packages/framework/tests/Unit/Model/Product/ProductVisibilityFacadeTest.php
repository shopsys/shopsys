<?php

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\ProductChangeMessageProducer;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

class ProductVisibilityFacadeTest extends TestCase
{
    public function testRefreshProductsVisibility()
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock->expects($this->once())->method('refreshProductsVisibility');

        $productChangeMessageProducer = $this->createMock(ProductChangeMessageProducer::class);

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock, $productChangeMessageProducer);
        $productVisibilityFacade->refreshProductsVisibility();
    }
}
