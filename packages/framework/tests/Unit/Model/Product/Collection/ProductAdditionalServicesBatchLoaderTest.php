<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Collection;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServiceFacade;
use Shopsys\FrameworkBundle\Model\Product\Collection\Exception\ProductAdditionalServicesNotLoadedException;
use Shopsys\FrameworkBundle\Model\Product\Collection\ProductAdditionalServicesBatchLoader;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductAdditionalServicesBatchLoaderTest extends TestCase
{
    public function testSpecialServiceNamesAreCappedAtFive(): void
    {
        $additionalServices = [];

        for ($serviceNumber = 1; $serviceNumber <= 6; $serviceNumber++) {
            $additionalServices[] = $this->createAdditionalServiceStub('Service ' . $serviceNumber);
        }

        $product = $this->createProductStub(1);
        $domainConfig = $this->createDomainConfigStub();
        $productAdditionalServicesBatchLoader = $this->createProductAdditionalServicesBatchLoader([1 => $additionalServices]);

        $productAdditionalServicesBatchLoader->loadShownInFeedsForProducts([$product], $domainConfig);

        self::assertSame(
            ['Service 1', 'Service 2', 'Service 3', 'Service 4', 'Service 5'],
            $productAdditionalServicesBatchLoader->getShownInFeedsSpecialServiceNames($product, $domainConfig),
        );
    }

    public function testServicesWithoutFeedNameAreSkipped(): void
    {
        $product = $this->createProductStub(1);
        $domainConfig = $this->createDomainConfigStub();
        $productAdditionalServicesBatchLoader = $this->createProductAdditionalServicesBatchLoader([
            1 => [
                $this->createAdditionalServiceStub('Assembly'),
                $this->createAdditionalServiceStub(null),
            ],
        ]);

        $productAdditionalServicesBatchLoader->loadShownInFeedsForProducts([$product], $domainConfig);

        self::assertSame(
            ['Assembly'],
            $productAdditionalServicesBatchLoader->getShownInFeedsFeedNames($product, $domainConfig),
        );
    }

    public function testLoadedProductWithoutServicesReturnsEmptyArray(): void
    {
        $product = $this->createProductStub(1);
        $domainConfig = $this->createDomainConfigStub();
        $productAdditionalServicesBatchLoader = $this->createProductAdditionalServicesBatchLoader([]);

        $productAdditionalServicesBatchLoader->loadShownInFeedsForProducts([$product], $domainConfig);

        self::assertSame(
            [],
            $productAdditionalServicesBatchLoader->getShownInFeedsAdditionalServices($product, $domainConfig),
        );
    }

    public function testNotLoadedProductThrowsException(): void
    {
        $product = $this->createProductStub(1);
        $domainConfig = $this->createDomainConfigStub();
        $productAdditionalServicesBatchLoader = $this->createProductAdditionalServicesBatchLoader([]);

        $this->expectException(ProductAdditionalServicesNotLoadedException::class);

        $productAdditionalServicesBatchLoader->getShownInFeedsAdditionalServices($product, $domainConfig);
    }

    /**
     * @param array<int, \Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService[]> $additionalServicesIndexedByProductId
     */
    private function createProductAdditionalServicesBatchLoader(
        array $additionalServicesIndexedByProductId,
    ): ProductAdditionalServicesBatchLoader {
        $additionalServiceFacadeStub = $this->createStub(AdditionalServiceFacade::class);
        $additionalServiceFacadeStub->method('getShownInFeedsIndexedByProductIds')
            ->willReturn($additionalServicesIndexedByProductId);

        return new ProductAdditionalServicesBatchLoader($additionalServiceFacadeStub, new InMemoryCache());
    }

    private function createProductStub(int $productId): Product
    {
        $productStub = $this->createStub(Product::class);
        $productStub->method('getId')->willReturn($productId);

        return $productStub;
    }

    private function createDomainConfigStub(): DomainConfig
    {
        $domainConfigStub = $this->createStub(DomainConfig::class);
        $domainConfigStub->method('getId')->willReturn(1);
        $domainConfigStub->method('getLocale')->willReturn('en');
        $domainConfigStub->method('getName')->willReturn('shopsys');

        return $domainConfigStub;
    }

    private function createAdditionalServiceStub(?string $feedName): AdditionalService
    {
        $additionalServiceStub = $this->createStub(AdditionalService::class);
        $additionalServiceStub->method('getFeedName')->willReturn($feedName);

        return $additionalServiceStub;
    }
}
