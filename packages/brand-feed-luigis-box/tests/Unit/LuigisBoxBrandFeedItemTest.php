<?php

declare(strict_types=1);

namespace Tests\BrandFeed\LuigisBoxBundle\Unit;

use PHPUnit\Framework\TestCase;
use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItem;
use Shopsys\BrandFeed\LuigisBoxBundle\Model\LuigisBoxBrandFeedItemFactory;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Image\ImageUrlWithSizeHelper;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;

class LuigisBoxBrandFeedItemTest extends TestCase
{
    private const BRAND_ID = 1;
    private const BRAND_NAME = 'Test brand';
    private const BRAND_URL = 'https://www.example.com/test-brand';
    private const BRAND_IMAGE_URL = 'https://www.example.com/test-brand.jpg';

    public function testBrandFeedItemCreation(): void
    {
        $defaultDomain = $this->createDomainConfigStub(
            Domain::FIRST_DOMAIN_ID,
            'https://example.com',
            'en',
        );

        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(self::BRAND_ID);
        $brand->method('getName')->willReturn(self::BRAND_NAME);

        $friendlyUrlFacadeMock = $this->createMock(FriendlyUrlFacade::class);
        $friendlyUrlFacadeMock->expects($this->any())->method('getAbsoluteUrlByRouteNameAndEntityId')
            ->with(Domain::FIRST_DOMAIN_ID, 'front_brand_detail', self::BRAND_ID)->willReturn(self::BRAND_URL);

        $imageFacadeMock = $this->createMock(ImageFacade::class);
        $imageFacadeMock->expects($this->any())->method('getImageUrl')
            ->with($defaultDomain, $brand)->willReturn(self::BRAND_IMAGE_URL);

        $luigisBoxBrandFeedItemFactory = new LuigisBoxBrandFeedItemFactory($friendlyUrlFacadeMock, $imageFacadeMock, new ImageUrlWithSizeHelper());
        $luigisBoxBrandFeedItem = $luigisBoxBrandFeedItemFactory->create($brand, $defaultDomain);

        $this->assertSame(LuigisBoxBrandFeedItem::UNIQUE_BRAND_IDENTIFIER_PREFIX . '-' . self::BRAND_ID, $luigisBoxBrandFeedItem->getIdentity());
        $this->assertSame(self::BRAND_NAME, $luigisBoxBrandFeedItem->getName());
        $this->assertSame(self::BRAND_URL, $luigisBoxBrandFeedItem->getUrl());
        $this->assertSame(self::BRAND_IMAGE_URL . '?width=100&height=100', $luigisBoxBrandFeedItem->getImageUrl());
    }

    private function createDomainConfigStub(int $id, string $url, string $locale): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);

        $domainConfigStub->method('getId')->willReturn($id);
        $domainConfigStub->method('getUrl')->willReturn($url);
        $domainConfigStub->method('getLocale')->willReturn($locale);

        return $domainConfigStub;
    }
}
