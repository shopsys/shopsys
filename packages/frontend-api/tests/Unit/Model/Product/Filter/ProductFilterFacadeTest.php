<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product\Filter;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterBatchLoadDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfig;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterConfigFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterDataMapper;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterNormalizer;

class ProductFilterFacadeTest extends TestCase
{
    private const string LOCALE = 'en';

    public function testConfigsCreatedEarlierInRequestAreServedFromCacheAndOnlyMissingOnesAreCreatedInBatch(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(5);
        $category = $this->createStub(Category::class);
        $category->method('getId')->willReturn(1);
        $brandConfig = $this->createStub(ProductFilterConfig::class);
        $categoryConfig = $this->createStub(ProductFilterConfig::class);

        $productFilterConfigFactory = $this->createMock(ProductFilterConfigFactory::class);
        $productFilterConfigFactory->expects($this->exactly(2))
            ->method('createByBatchLoadData')
            ->with($this->anything(), self::LOCALE)
            ->willReturnCallback(function (array $batchLoadDataIndexedByKey) use ($brand, $category, $brandConfig, $categoryConfig): array {
                $this->assertCount(1, $batchLoadDataIndexedByKey);
                $batchLoadData = array_first($batchLoadDataIndexedByKey);
                $key = array_key_first($batchLoadDataIndexedByKey);

                return match (true) {
                    $batchLoadData->getEntity() === $brand => [
                        $key => $brandConfig,
                    ],
                    $batchLoadData->getEntity() === $category => [
                        $key => $categoryConfig,
                    ],
                    default => self::fail('Unexpected batch load data'),
                };
            });

        $productFilterFacade = $this->createProductFilterFacade($productFilterConfigFactory);

        $this->assertSame(['brand' => $brandConfig], $productFilterFacade->getProductFilterConfigsByBatchLoadData(['brand' => new ProductFilterBatchLoadData($brand, '')]));

        $productFilterConfigsIndexedByKey = $productFilterFacade->getProductFilterConfigsByBatchLoadData([
            'brand' => new ProductFilterBatchLoadData($brand, ''),
            'category' => new ProductFilterBatchLoadData($category, ''),
        ]);

        $this->assertSame([
            'brand' => $brandConfig,
            'category' => $categoryConfig,
        ], $productFilterConfigsIndexedByKey);
        $this->assertSame(['category' => $categoryConfig], $productFilterFacade->getProductFilterConfigsByBatchLoadData(['category' => new ProductFilterBatchLoadData($category, '')]));
    }

    public function testSearchAndAllConfigsAreCachedByTheirOwnKeys(): void
    {
        $searchConfig = $this->createStub(ProductFilterConfig::class);
        $allConfig = $this->createStub(ProductFilterConfig::class);

        $productFilterConfigFactory = $this->createMock(ProductFilterConfigFactory::class);
        $productFilterConfigFactory->expects($this->once())
            ->method('createByBatchLoadData')
            ->with($this->callback(static fn (array $batchLoadDataIndexedByCacheKey): bool => array_keys($batchLoadDataIndexedByCacheKey) === ['search~phone', 'all']), self::LOCALE)
            ->willReturn(['search~phone' => $searchConfig, 'all' => $allConfig]);

        $productFilterFacade = $this->createProductFilterFacade($productFilterConfigFactory);

        $productFilterConfigsIndexedByKey = $productFilterFacade->getProductFilterConfigsByBatchLoadData([
            'search' => new ProductFilterBatchLoadData(null, 'phone'),
            'all' => new ProductFilterBatchLoadData(null, ''),
        ]);

        $this->assertSame(['search' => $searchConfig, 'all' => $allConfig], $productFilterConfigsIndexedByKey);
        $this->assertSame($allConfig, $productFilterFacade->getProductFilterConfigForAll());
    }

    public function testSameListingRequestedTwiceInOneBatchIsCreatedOnce(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(5);
        $brandConfig = $this->createStub(ProductFilterConfig::class);

        $productFilterConfigFactory = $this->createMock(ProductFilterConfigFactory::class);
        $productFilterConfigFactory->expects($this->once())
            ->method('createByBatchLoadData')
            ->with($this->callback(static fn (array $batchLoadDataIndexedByCacheKey): bool => array_keys($batchLoadDataIndexedByCacheKey) === ['brand~5']), self::LOCALE)
            ->willReturn(['brand~5' => $brandConfig]);

        $productFilterFacade = $this->createProductFilterFacade($productFilterConfigFactory);

        $productFilterConfigsIndexedByKey = $productFilterFacade->getProductFilterConfigsByBatchLoadData([
            'first' => new ProductFilterBatchLoadData($brand, ''),
            'second' => new ProductFilterBatchLoadData($brand, ''),
        ]);

        $this->assertSame(['first' => $brandConfig, 'second' => $brandConfig], $productFilterConfigsIndexedByKey);
    }

    public function testConfigsOfWholeBatchAreCreatedOnceAndOnlyFilterDataWithFiltersAreNormalized(): void
    {
        $brand = $this->createStub(Brand::class);
        $brand->method('getId')->willReturn(5);
        $flag = $this->createStub(Flag::class);
        $flag->method('getId')->willReturn(7);
        $brandConfig = $this->createStub(ProductFilterConfig::class);
        $flagConfig = $this->createStub(ProductFilterConfig::class);
        $brandFilterData = new ProductFilterData();
        $brandFilterData->flags = [$this->createStub(Flag::class)];
        $flagFilterData = new ProductFilterData();

        $productFilterConfigFactory = $this->createMock(ProductFilterConfigFactory::class);
        $productFilterConfigFactory->expects($this->once())
            ->method('createByBatchLoadData')
            ->with($this->callback(static fn (array $batchLoadDataIndexedByCacheKey): bool => array_keys($batchLoadDataIndexedByCacheKey) === ['brand~5', 'flag~7']), self::LOCALE)
            ->willReturn(['brand~5' => $brandConfig, 'flag~7' => $flagConfig]);

        $productFilterNormalizer = $this->createMock(ProductFilterNormalizer::class);
        $productFilterNormalizer->expects($this->once())
            ->method('removeExcessiveFilters')
            ->with($brandFilterData, $brandConfig);

        $productFilterFacade = $this->createProductFilterFacade($productFilterConfigFactory, $productFilterNormalizer);

        $productFilterFacade->normalizeProductFilterDataByBatchLoadData([
            new ProductFilterDataBatchLoadData($brand, '', $brandFilterData),
            new ProductFilterDataBatchLoadData($flag, '', $flagFilterData),
        ]);

        $this->assertSame([], $flagFilterData->flags);
        $this->assertSame(['flag' => $flagConfig], $productFilterFacade->getProductFilterConfigsByBatchLoadData(['flag' => new ProductFilterBatchLoadData($flag, '')]));
    }

    public function testListingFlagIsAddedToFilterDataOnlyOnce(): void
    {
        $flag = $this->createStub(Flag::class);
        $otherFlag = $this->createStub(Flag::class);
        $productFilterData = new ProductFilterData();
        $productFilterData->flags = [$otherFlag];

        $productFilterFacade = $this->createProductFilterFacade($this->createStub(ProductFilterConfigFactory::class));

        $productFilterFacade->addListingFlagToProductFilterData($flag, $productFilterData);
        $productFilterFacade->addListingFlagToProductFilterData($flag, $productFilterData);

        $this->assertSame([$otherFlag, $flag], $productFilterData->flags);
    }

    private function createProductFilterFacade(
        ProductFilterConfigFactory $productFilterConfigFactory,
        ?ProductFilterNormalizer $productFilterNormalizer = null,
    ): ProductFilterFacade {
        $domain = $this->createStub(Domain::class);
        $domain->method('getLocale')->willReturn(self::LOCALE);

        return new ProductFilterFacade(
            $domain,
            $this->createStub(ProductFilterDataMapper::class),
            $productFilterNormalizer ?? $this->createStub(ProductFilterNormalizer::class),
            $productFilterConfigFactory,
            $this->createStub(ProductFilterDataFactory::class),
            $this->createStub(CustomerUserRoleResolver::class),
            new InMemoryCache(),
            new ProductFilterBatchLoadDataFactory(),
        );
    }
}
