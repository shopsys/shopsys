<?php

declare(strict_types=1);

namespace Tests\ReadModelBundle\Unit\Brand;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\ReadModelBundle\Brand\BrandView;
use Shopsys\ReadModelBundle\Brand\BrandViewFactory;

class BrandViewFactoryTest extends TestCase
{
    public function testCreateFromBrand(): void
    {
        $id = 1;
        $name = 'Brand';
        $mainUrl = 'https://webserver:8080/brand';

        $brandMock = $this->createMock(Brand::class);
        $brandMock->method('getId')->willReturn($id);
        $brandMock->method('getName')->willReturn($name);

        $brandViewFactory = new BrandViewFactory();

        $brandView = $brandViewFactory->createFromBrand($brandMock, $mainUrl);

        self::assertEquals(
            new BrandView($id, $name, $mainUrl),
            $brandView
        );
    }
}
