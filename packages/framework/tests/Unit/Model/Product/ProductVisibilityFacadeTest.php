<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

class ProductVisibilityFacadeTest extends TestCase
{
    public function testRefreshProductsVisibility(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock->expects($this->once())->method('refreshProductsVisibility');

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);
        $productVisibilityFacade->calculateProductVisibilityForAll();
    }

    public function testRefreshProductsVisibilityForMarked(): void
    {
        $productIds = [1, 2, 3];

        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock
            ->expects($this->once())
            ->method('refreshProductsVisibility')
            ->with($this->equalTo($productIds));

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock);
        $productVisibilityFacade->calculateProductVisibilityForIds($productIds);
    }
}
