<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\PromoCode;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\ProductPromoCodeFiller;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeBrand\PromoCodeBrandRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeCategory\PromoCodeCategoryRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFlag\PromoCodeFlagRepository;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeProduct\PromoCodeProductRepository;

class ProductPromoCodeFillerTest extends TestCase
{
    public function testAllowedProductIdsAreLoadedOncePerPromoCode(): void
    {
        $promoCodeProductRepositoryMock = $this->createMock(PromoCodeProductRepository::class);
        $promoCodeProductRepositoryMock->expects($this->exactly(2))->method('getProductIdsByPromoCodeId')->willReturnCallback(
            static fn (int $promoCodeId): array => [$promoCodeId * 10],
        );
        $productPromoCodeFiller = $this->createProductPromoCodeFiller($promoCodeProductRepositoryMock);

        $this->assertSame([70], $productPromoCodeFiller->getAllowedProductIds($this->createPromoCodeStub(7)));
        $this->assertSame([70], $productPromoCodeFiller->getAllowedProductIds($this->createPromoCodeStub(7)));
        $this->assertSame([80], $productPromoCodeFiller->getAllowedProductIds($this->createPromoCodeStub(8)));
    }

    private function createProductPromoCodeFiller(
        PromoCodeProductRepository $promoCodeProductRepository,
    ): ProductPromoCodeFiller {
        return new ProductPromoCodeFiller(
            $promoCodeProductRepository,
            $this->createStub(PromoCodeCategoryRepository::class),
            $this->createStub(PromoCodeBrandRepository::class),
            $this->createStub(PromoCodeFlagRepository::class),
            new InMemoryCache(),
        );
    }

    private function createPromoCodeStub(int $id): PromoCode
    {
        $promoCodeStub = $this->createStub(PromoCode::class);
        $promoCodeStub->method('getId')->willReturn($id);

        return $promoCodeStub;
    }
}
