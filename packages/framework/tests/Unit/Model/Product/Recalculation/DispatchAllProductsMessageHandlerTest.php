<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Recalculation;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessage;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessageHandler;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum;

class DispatchAllProductsMessageHandlerTest extends TestCase
{
    /**
     * @return iterable<string, array{productIds: int[], expectedDispatchedProductIds: int[][]}>
     */
    public static function productIdsProvider(): iterable
    {
        yield 'no products' => [
            'productIds' => [],
            'expectedDispatchedProductIds' => [],
        ];

        yield 'fewer products than batch size' => [
            'productIds' => [1],
            'expectedDispatchedProductIds' => [[1]],
        ];

        yield 'products filling whole batches' => [
            'productIds' => [1, 2, 3, 4],
            'expectedDispatchedProductIds' => [[1, 2], [3, 4]],
        ];

        yield 'products with incomplete last batch' => [
            'productIds' => [1, 2, 3, 4, 5],
            'expectedDispatchedProductIds' => [[1, 2], [3, 4], [5]],
        ];
    }

    /**
     * @param int[] $productIds
     * @param int[][] $expectedDispatchedProductIds
     */
    #[DataProvider('productIdsProvider')]
    public function testAllProductsAreDispatchedInBatches(array $productIds, array $expectedDispatchedProductIds): void
    {
        $exportScopes = ['product_name_scope'];

        $productFacadeStub = $this->createStub(ProductFacade::class);
        $productFacadeStub->method('iterateAllProductIdsExceptVariant')
            ->willReturn(array_map(static fn (int $productId): array => ['id' => $productId], $productIds));

        $dispatchedProductIds = [];
        $productRecalculationDispatcherMock = $this->createMock(ProductRecalculationDispatcher::class);
        $productRecalculationDispatcherMock->expects($this->exactly(count($expectedDispatchedProductIds)))
            ->method('dispatchProductIds')
            ->willReturnCallback(
                function (array $productIdsToDispatch, string $priority, array $dispatchedExportScopes) use (&$dispatchedProductIds, $exportScopes): void {
                    $this->assertSame(ProductRecalculationPriorityEnum::REGULAR, $priority);
                    $this->assertSame($exportScopes, $dispatchedExportScopes);
                    $dispatchedProductIds[] = $productIdsToDispatch;
                },
            );

        $dispatchAllProductsMessageHandler = new class($productFacadeStub, $productRecalculationDispatcherMock) extends DispatchAllProductsMessageHandler {
            protected const int PRODUCT_IDS_BATCH_SIZE = 2;
        };
        $dispatchAllProductsMessageHandler(new DispatchAllProductsMessage($exportScopes));

        $this->assertSame($expectedDispatchedProductIds, $dispatchedProductIds);
    }
}
