<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;
use function sleep;

class ProductCurrentlyOutOfStockTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    /**
     * @return iterable<string, array{isAllowedNegativeStock: bool, setZeroStock: bool, expectedIsCurrentlyOutOfStock: bool}>
     */
    public static function currentlyOutOfStockDataProvider(): iterable
    {
        yield 'not allowed negative stock with zero stock' => [
            'isAllowedNegativeStock' => false,
            'setZeroStock' => true,
            'expectedIsCurrentlyOutOfStock' => true,
        ];

        yield 'allowed negative stock with zero stock' => [
            'isAllowedNegativeStock' => true,
            'setZeroStock' => true,
            'expectedIsCurrentlyOutOfStock' => false,
        ];

        yield 'not allowed negative stock with positive stock' => [
            'isAllowedNegativeStock' => false,
            'setZeroStock' => false,
            'expectedIsCurrentlyOutOfStock' => false,
        ];

        yield 'allowed negative stock with positive stock' => [
            'isAllowedNegativeStock' => true,
            'setZeroStock' => false,
            'expectedIsCurrentlyOutOfStock' => false,
        ];
    }

    #[DataProvider('currentlyOutOfStockDataProvider')]
    public function testIsCurrentlyOutOfStockDependsOnNegativeStockSettings(
        bool $isAllowedNegativeStock,
        bool $setZeroStock,
        bool $expectedIsCurrentlyOutOfStock,
    ): void {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        /** @var \App\Model\Product\ProductData $productData */
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->isAllowedNegativeStock = $isAllowedNegativeStock;

        foreach ($productData->productStockData as $productStockData) {
            $productStockData->productQuantity = $setZeroStock === true ? 0 : 1;
        }

        $this->productFacade->edit($product->getId(), $productData);

        $this->handleDispatchedRecalculationMessages();

        // wait for elastic to reindex
        sleep(1);

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/query/ProductQuery.graphql', [
            'uuid' => $product->getUuid(),
        ]);

        $responseData = $this->getResponseDataForGraphQlType($response, 'product');

        self::assertSame($expectedIsCurrentlyOutOfStock, $responseData['isCurrentlyOutOfStock']);
    }
}
