<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductListProductsTest extends GraphQlTestCase
{
    use ProductListProductIdsTrait;

    private const array PRODUCT_IDS = [49, 33];

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testVisibleSellingDeniedProductRemainsInListAndCount(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_IDS[0], Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainSellingDenied[$this->domain->getId()] = true;
        $this->editProductAndRefreshIndex($product, $productData);

        $data = $this->assertProductListCountMatchesProducts($productListType, $productListUuid, self::PRODUCT_IDS);

        $this->assertTrue($data['products']['edges'][0]['node']['isSellingDenied']);
    }

    public function testProductsConnectionIsPaginatedInListOrder(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $firstPage = $this->getProductsConnectionData($productListType, $productListUuid, 1);

        $this->assertSame($this->getProductUuids(self::PRODUCT_IDS), $firstPage['productUuids']);
        $this->assertSame('POSITION', $firstPage['products']['orderingMode']);
        $this->assertSame('POSITION', $firstPage['products']['defaultOrderingMode']);

        $this->assertSame([self::PRODUCT_IDS[0]], $this->getProductIds($firstPage));
        $this->assertSame(count(self::PRODUCT_IDS), $firstPage['products']['totalCount']);
        $this->assertTrue($firstPage['products']['pageInfo']['hasNextPage']);

        $secondPage = $this->getProductsConnectionData(
            $productListType,
            $productListUuid,
            1,
            $firstPage['products']['pageInfo']['endCursor'],
        );

        $this->assertSame([self::PRODUCT_IDS[1]], $this->getProductIds($secondPage));
        $this->assertSame(count(self::PRODUCT_IDS), $secondPage['products']['totalCount']);
        $this->assertFalse($secondPage['products']['pageInfo']['hasNextPage']);
    }

    public function testTotalCountIsAvailableWithoutLoadingAnyProduct(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $data = $this->getProductsConnectionData($productListType, $productListUuid, 0);

        $this->assertSame([], $data['products']['edges']);
        $this->assertSame(count(self::PRODUCT_IDS), $data['products']['totalCount']);
    }

    public function testHiddenProductIsExcludedFromPagination(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $this->hideProductOnCurrentDomain(self::PRODUCT_IDS[0]);

        $data = $this->getProductsConnectionData($productListType, $productListUuid, 1);

        $this->assertSame([self::PRODUCT_IDS[1]], $this->getProductIds($data));
        $this->assertSame(1, $data['products']['totalCount']);
        $this->assertFalse($data['products']['pageInfo']['hasNextPage']);
        $this->assertSame($this->getProductUuids(self::PRODUCT_IDS), $data['productUuids']);
    }

    public function testPageSizeOverMaximumIsRejected(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListProductsQuery.graphql', [
            'uuid' => $productListUuid,
            'type' => $productListType,
            'first' => 101,
        ]);

        $this->assertUserError($response, 'max-allowed-limit');
    }

    public function testProductFilterOptionsAreNotSupported(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListProductFilterOptionsQuery.graphql', [
            'uuid' => $productListUuid,
            'type' => $productListType,
        ]);

        $this->assertUserError($response, 'product-filter-options-not-supported');
    }

    private function createProductList(string $productListType): string
    {
        $productListUuid = Uuid::uuid4()->toString();

        foreach (array_reverse(self::PRODUCT_IDS) as $productId) {
            $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
                'productListUuid' => $productListUuid,
                'productUuid' => $product->getUuid(),
                'type' => $productListType,
            ]);
            $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');
            $this->assertSame($productListUuid, $data['uuid']);
        }

        return $productListUuid;
    }

    /**
     * @param int[] $productIds
     * @return string[]
     */
    private function getProductUuids(array $productIds): array
    {
        return array_map(
            fn (int $productId): string => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class)->getUuid(),
            $productIds,
        );
    }

    private function hideProductOnCurrentDomain(int $productId): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainHidden[$this->domain->getId()] = true;
        $this->editProductAndRefreshIndex($product, $productData);
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductsConnectionData(
        string $productListType,
        string $productListUuid,
        int $first,
        ?string $after = null,
    ): array {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListProductsQuery.graphql', [
            'uuid' => $productListUuid,
            'type' => $productListType,
            'first' => $first,
            'after' => $after,
        ]);

        return $this->getResponseDataForGraphQlType($response, 'productList');
    }

    /**
     * @param int[] $expectedProductIds
     * @return array<string, mixed>
     */
    private function assertProductListCountMatchesProducts(
        string $productListType,
        string $productListUuid,
        array $expectedProductIds,
    ): array {
        $variables = ['uuid' => $productListUuid, 'type' => $productListType];
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', $variables);
        $data = $this->getResponseDataForGraphQlType($response, 'productList');

        $this->assertSame($expectedProductIds, $this->getProductIds($data));
        $this->assertSame(count($expectedProductIds), $data['products']['totalCount']);

        return $data;
    }

    private function editProductAndRefreshIndex(Product $product, ProductData $productData): void
    {
        $this->productFacade->edit($product->getId(), $productData);
        $this->handleDispatchedRecalculationMessages([$product->getId()]);
        $indexDefinition = $this->indexDefinitionLoader->getIndexDefinition(ExtendedClassNameResolver::resolve(ProductIndex::class)::getName(), $this->domain->getId());
        $this->elasticsearchClient->indices()->refresh(['index' => $indexDefinition->getIndexAlias()]);
    }
}
