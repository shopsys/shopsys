<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product;

use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Adapter\SyncPromiseQueue;
use GraphQL\Executor\Promise\Promise;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Category\Category;
use Shopsys\FrameworkBundle\Model\CategorySeo\ReadyCategorySeoMix;
use Shopsys\FrameworkBundle\Model\Product\Brand\Brand;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadData;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataBatchLoadDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Filter\ProductFilterDataFactory;
use Shopsys\FrameworkBundle\Model\Product\Flag\Flag;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductBatchLoadByEntityData;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchProvider;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader;
use Shopsys\FrontendApiBundle\Model\Product\Filter\ProductFilterFacade;

class ProductsBatchLoaderTest extends TestCase
{
    private SyncPromiseAdapter $promiseAdapter;

    private ProductElasticsearchBatchProvider|MockObject $productElasticsearchBatchProvider;

    private Domain $domainStub;

    private GiftPlanFacade|MockObject $giftPlanFacade;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promiseAdapter = new SyncPromiseAdapter();
        $this->productElasticsearchBatchProvider = $this->createMock(ProductElasticsearchBatchProvider::class);
        $this->domainStub = $this->createStub(Domain::class);
        $this->giftPlanFacade = $this->createMock(GiftPlanFacade::class);

        $this->domainStub->method('getId')->willReturn(1);
    }

    public function testNoGiftsReturnsEmptyArraysInOrder(): void
    {
        $loader = $this->createLoader();

        $productIds = [10, 20, 30];

        // The facade returns a map without gifts for all input products
        $this->giftPlanFacade->expects($this->any())->method('findActiveGiftProductIdsByMainProductIds')
            ->with($productIds, 1)
            ->willReturn([
                10 => [],
                20 => [],
                30 => [],
            ]);

        // The provider must not be called at all because there are no gift IDs
        $this->productElasticsearchBatchProvider->expects($this->never())
            ->method('getBatchedProductGiftsAndIndexedByProductsIds');

        $promise = $loader->loadProductGiftsByMainProductIds($productIds);

        $result = $this->resolvePromise($promise);

        // The result must have the same indexes (0..n-1) and empty lists
        $this->assertSame([[], [], []], $result);
    }

    public function testSharedGiftIdMappedToMultipleProductsWithoutDuplicatesAndMaintainsOrder(): void
    {
        $loader = $this->createLoader();

        // The input order is important
        $productIds = [50, 40, 60];

        // 100 is a shared gift for 50 and 40, 110 only for 60
        $this->giftPlanFacade->expects($this->any())->method('findActiveGiftProductIdsByMainProductIds')
            ->with($productIds, 1)
            ->willReturn([
                50 => [100],
                40 => [100],
                60 => [110],
            ]);

        // The provider returns entities for gift IDs, indexed by the constant
        $this->productElasticsearchBatchProvider->expects($this->once())
            ->method('getBatchedProductGiftsAndIndexedByProductsIds')
            ->with([
                50 => [100],
                40 => [100],
                60 => [110],
            ])
            ->willReturn([
                ProductElasticsearchBatchRepository::PRODUCTS_KEY => [
                    50 => [
                        100 => ['id' => 100, 'name' => 'Gift 100'],
                    ],
                    40 => [
                        100 => ['id' => 100, 'name' => 'Gift 100'],
                    ],
                    60 => [
                        110 => ['id' => 110, 'name' => 'Gift 110'],
                    ],
                ],
            ]);

        $promise = $loader->loadProductGiftsByMainProductIds($productIds);

        $result = $this->resolvePromise($promise);

        // Preserving the input order and gift mapping
        $this->assertSame(
            [
                0 => [100 => ['id' => 100, 'name' => 'Gift 100']], // for productId 50
                1 => [100 => ['id' => 100, 'name' => 'Gift 100']], // for productId 40
                2 => [110 => ['id' => 110, 'name' => 'Gift 110']], // for productId 60
            ],
            $result,
        );
    }

    public function testMissingGiftEntitiesAreIgnored(): void
    {
        $loader = $this->createLoader();

        $productIds = [70];

        // The facade returns gift ID 200, but the provider does not "know" it
        $this->giftPlanFacade->expects($this->any())->method('findActiveGiftProductIdsByMainProductIds')
            ->with($productIds, 1)
            ->willReturn([
                70 => [200, 210],
            ]);

        // The provider returns only 210, 200 is missing => it must be ignored
        $this->productElasticsearchBatchProvider->expects($this->once())
            ->method('getBatchedProductGiftsAndIndexedByProductsIds')
            ->with([
                70 => [200, 210],
            ])
            ->willReturn([
                ProductElasticsearchBatchRepository::PRODUCTS_KEY => [
                    70 => [210 => ['id' => 210, 'name' => 'Gift 210']],
                ],
            ]);

        $promise = $loader->loadProductGiftsByMainProductIds($productIds);

        $result = $this->resolvePromise($promise);

        $this->assertSame(
            [
                0 => [
                    210 => ['id' => 210, 'name' => 'Gift 210'],
                ],
            ],
            $result,
        );
    }

    public function testEmptyFacadeMapStillReturnsEmptyArraysForAllInputs(): void
    {
        $loader = $this->createLoader();

        $productIds = [1, 2, 3, 4];

        // The facade returns an empty map (edge case)
        $this->giftPlanFacade->expects($this->any())->method('findActiveGiftProductIdsByMainProductIds')
            ->with($productIds, 1)
            ->willReturn([]);

        $this->productElasticsearchBatchProvider->expects($this->never())
            ->method('getBatchedProductGiftsAndIndexedByProductsIds');

        $promise = $loader->loadProductGiftsByMainProductIds($productIds);

        $result = $this->resolvePromise($promise);

        $this->assertSame([[], [], [], []], $result);
    }

    public function testFilterDataOfAllEntitiesAreNormalizedInOneBatchBeforeProductsAreLoaded(): void
    {
        $firstBrand = $this->createStub(Brand::class);
        $secondBrand = $this->createStub(Brand::class);
        $firstFilterData = new ProductFilterData();
        $secondFilterData = new ProductFilterData();
        $firstBatchLoadData = new ProductBatchLoadByEntityData('first', $firstBrand, 10, 0, 'priority', $firstFilterData);
        $secondBatchLoadData = new ProductBatchLoadByEntityData('second', $secondBrand, 10, 0, 'priority', $secondFilterData);
        $batchLoadData = [$firstBatchLoadData, $secondBatchLoadData];

        $this->giftPlanFacade->expects($this->never())->method('findActiveGiftProductIdsByMainProductIds');

        $productFilterFacade = $this->createMock(ProductFilterFacade::class);
        $productFilterFacade->expects($this->once())
            ->method('normalizeProductFilterDataByBatchLoadData')
            ->with($this->callback(function (array $productFilterBatchLoadData) use ($firstBrand, $secondBrand, $firstFilterData, $secondFilterData): bool {
                $this->assertCount(2, $productFilterBatchLoadData);
                $this->assertContainsOnlyInstancesOf(ProductFilterDataBatchLoadData::class, $productFilterBatchLoadData);
                $this->assertSame($firstBrand, $productFilterBatchLoadData[0]->getEntity());
                $this->assertSame($firstFilterData, $productFilterBatchLoadData[0]->getProductFilterData());
                $this->assertSame($secondBrand, $productFilterBatchLoadData[1]->getEntity());
                $this->assertSame($secondFilterData, $productFilterBatchLoadData[1]->getProductFilterData());

                return true;
            }));

        $this->productElasticsearchBatchProvider->expects($this->once())
            ->method('getBatchedByEntities')
            ->with($batchLoadData)
            ->willReturn([
                ProductElasticsearchBatchRepository::PRODUCTS_KEY => [
                    'first' => [['id' => 1]],
                    'second' => [['id' => 2]],
                ],
                ProductElasticsearchBatchRepository::TOTALS_KEY => [
                    'first' => 1,
                    'second' => 1,
                ],
            ]);

        $loader = $this->createLoader($productFilterFacade);

        $this->assertSame([[['id' => 1]], [['id' => 2]]], $this->resolvePromise($loader->loadByEntities($batchLoadData)));
        $this->assertSame(1, ProductsBatchLoader::getTotalByBatchLoadDataId('second'));
    }

    public function testListingFlagAndReadyCategorySeoMixAreAddedToFilterDataAfterNormalization(): void
    {
        $flag = $this->createStub(Flag::class);
        $readyCategorySeoMix = $this->createStub(ReadyCategorySeoMix::class);
        $flagFilterData = new ProductFilterData();
        $categoryFilterData = new ProductFilterData();
        $brandFilterData = new ProductFilterData();
        $batchLoadData = [
            new ProductBatchLoadByEntityData('flag', $flag, 10, 0, 'priority', $flagFilterData),
            new ProductBatchLoadByEntityData('category', $this->createStub(Category::class), 10, 0, 'priority', $categoryFilterData, '', $readyCategorySeoMix),
            new ProductBatchLoadByEntityData('brand', $this->createStub(Brand::class), 10, 0, 'priority', $brandFilterData),
        ];
        $isNormalized = false;

        $this->giftPlanFacade->expects($this->never())->method('findActiveGiftProductIdsByMainProductIds');

        $productFilterFacade = $this->createMock(ProductFilterFacade::class);
        $productFilterFacade->expects($this->once())
            ->method('normalizeProductFilterDataByBatchLoadData')
            ->willReturnCallback(function () use (&$isNormalized): void {
                $isNormalized = true;
            });
        $productFilterFacade->expects($this->once())
            ->method('addListingFlagToProductFilterData')
            ->with($flag, $flagFilterData)
            ->willReturnCallback(function () use (&$isNormalized): void {
                $this->assertTrue($isNormalized);
            });

        $productFilterDataFactory = $this->createMock(ProductFilterDataFactory::class);
        $productFilterDataFactory->expects($this->once())
            ->method('updateProductFilterDataFromReadyCategorySeoMix')
            ->with($readyCategorySeoMix, $categoryFilterData)
            ->willReturnCallback(function () use (&$isNormalized): void {
                $this->assertTrue($isNormalized);
            });

        $this->productElasticsearchBatchProvider->expects($this->once())
            ->method('getBatchedByEntities')
            ->with($batchLoadData)
            ->willReturn([
                ProductElasticsearchBatchRepository::PRODUCTS_KEY => ['flag' => [], 'category' => [], 'brand' => []],
                ProductElasticsearchBatchRepository::TOTALS_KEY => ['flag' => 0, 'category' => 0, 'brand' => 0],
            ]);

        $loader = $this->createLoader($productFilterFacade, $productFilterDataFactory);

        $this->assertSame([[], [], []], $this->resolvePromise($loader->loadByEntities($batchLoadData)));
    }

    private function createLoader(
        ?ProductFilterFacade $productFilterFacade = null,
        ?ProductFilterDataFactory $productFilterDataFactory = null,
    ): ProductsBatchLoader {
        return new ProductsBatchLoader(
            $this->promiseAdapter,
            $this->productElasticsearchBatchProvider,
            $this->domainStub,
            $this->giftPlanFacade,
            $productFilterFacade ?? $this->createStub(ProductFilterFacade::class),
            $productFilterDataFactory ?? $this->createStub(ProductFilterDataFactory::class),
            new ProductFilterDataBatchLoadDataFactory(),
        );
    }

    private function resolvePromise(Promise $promise): mixed
    {
        $resolved = null;

        $promise->then(function ($value) use (&$resolved): void {
            $resolved = $value;
        });

        // Flush the queue of synchronous promises (SyncPromise)
        SyncPromiseQueue::run();

        return $resolved;
    }
}
