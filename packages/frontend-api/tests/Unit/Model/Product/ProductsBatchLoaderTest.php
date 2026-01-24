<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Unit\Model\Product;

use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use GraphQL\Executor\Promise\Promise;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Product\GiftPlan\GiftPlanFacade;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchProvider;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductElasticsearchBatchRepository;
use Shopsys\FrontendApiBundle\Model\Product\BatchLoad\ProductsBatchLoader;

class ProductsBatchLoaderTest extends TestCase
{
    private SyncPromiseAdapter $promiseAdapter;

    private ProductElasticsearchBatchProvider|MockObject $productElasticsearchBatchProvider;

    private Domain|MockObject $domain;

    private GiftPlanFacade|MockObject $giftPlanFacade;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->promiseAdapter = new SyncPromiseAdapter();
        $this->productElasticsearchBatchProvider = $this->createMock(ProductElasticsearchBatchProvider::class);
        $this->domain = $this->createMock(Domain::class);
        $this->giftPlanFacade = $this->createMock(GiftPlanFacade::class);

        $this->domain->method('getId')->willReturn(1);
    }

    public function testNoGiftsReturnsEmptyArraysInOrder(): void
    {
        $loader = $this->createLoader();

        $productIds = [10, 20, 30];

        // The facade returns a map without gifts for all input products
        $this->giftPlanFacade->method('findActiveGiftProductIdsByMainProductIds')
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
        $this->giftPlanFacade->method('findActiveGiftProductIdsByMainProductIds')
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
        $this->giftPlanFacade->method('findActiveGiftProductIdsByMainProductIds')
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
        $this->giftPlanFacade->method('findActiveGiftProductIdsByMainProductIds')
            ->with($productIds, 1)
            ->willReturn([]);

        $this->productElasticsearchBatchProvider->expects($this->never())
            ->method('getBatchedProductGiftsAndIndexedByProductsIds');

        $promise = $loader->loadProductGiftsByMainProductIds($productIds);

        $result = $this->resolvePromise($promise);

        $this->assertSame([[], [], [], []], $result);
    }

    private function createLoader(): ProductsBatchLoader
    {
        return new ProductsBatchLoader(
            $this->promiseAdapter,
            $this->productElasticsearchBatchProvider,
            $this->domain,
            $this->giftPlanFacade,
        );
    }

    private function resolvePromise(Promise $promise): mixed
    {
        $resolved = null;

        $promise->then(function ($value) use (&$resolved): void {
            $resolved = $value;
        });

        // Flush the queue of synchronous promises (SyncPromise)
        SyncPromise::runQueue();

        return $resolved;
    }
}
