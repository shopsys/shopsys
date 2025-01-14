<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityFacade;
use Shopsys\FrameworkBundle\Model\Product\ProductVisibilityRepository;

class ProductVisibilityFacadeTest extends TestCase
{
    public function testRefreshProductsVisibility(): void
    {
        $productVisibilityRepositoryMock = $this->createMock(ProductVisibilityRepository::class);
        $productVisibilityRepositoryMock->expects($this->once())->method('refreshProductsVisibility');

        $domainMock = $this->createMock(Domain::class);
        $pricingGroupSettingFacadeMock = $this->createMock(PricingGroupSettingFacade::class);

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock, $domainMock, $pricingGroupSettingFacadeMock);
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

        $domainMock = $this->createMock(Domain::class);
        $pricingGroupSettingFacadeMock = $this->createMock(PricingGroupSettingFacade::class);

        $productVisibilityFacade = new ProductVisibilityFacade($productVisibilityRepositoryMock, $domainMock, $pricingGroupSettingFacadeMock);
        $productVisibilityFacade->calculateProductVisibilityForIds($productIds);
    }
}
