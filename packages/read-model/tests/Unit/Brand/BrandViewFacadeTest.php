<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Unit\Brand;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFacade;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;

class BrandViewFacadeTest extends TestCase
{
    public function testCreateFromProductWithBrand(): void
    {
        $id = 1;
        $name = 'Brand';
        $mainUrl = 'https://webserver:8080/brand';

        $brandMock = $this->createMock(Brand::class);
        $brandMock->method('getId')->willReturn($id);
        $brandMock->method('getName')->willReturn($name);

        $productFacadeMock = $this->createProductFacadeMock($brandMock);

        $friendlyUrlFacadeMock = $this->createMock(FriendlyUrlFacade::class);
        $friendlyUrlFacadeMock->method('getAbsoluteUrlByRouteNameAndEntityIdOnCurrentDomain')->willReturn($mainUrl);

        $brandViewFacade = new BrandViewFacade($productFacadeMock, new BrandViewFactory(), $friendlyUrlFacadeMock);

        self::assertEquals(
            new BrandView($id, $name, $mainUrl),
            $brandViewFacade->findByProductId($id)
        );
    }

    public function testCreateFromProductWithoutBrand(): void
    {
        $productFacadeMock = $this->createProductFacadeMock(null);

        $friendlyUrlFacadeMock = $this->createMock(FriendlyUrlFacade::class);

        $brandViewFacade = new BrandViewFacade($productFacadeMock, new BrandViewFactory(), $friendlyUrlFacadeMock);

        self::assertEquals(
            null,
            $brandViewFacade->findByProductId(1)
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Brand\Brand|null $brandMock
     * @return \Shopsys\FrameworkBundle\Model\Product\ProductFacade
     */
    public function createProductFacadeMock(?Brand $brandMock): ProductFacade
    {
        $productMock = $this->createMock(Product::class);
        $productMock->method('getBrand')->willReturn($brandMock);

        $productFacadeMock = $this->createMock(ProductFacade::class);
        $productFacadeMock->method('getById')->willReturn($productMock);
        return $productFacadeMock;
    }
}
