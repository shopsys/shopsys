<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use App\Model\Product\ProductData;
use App\Model\Product\ProductDataFactory;
use App\Model\Product\ProductFacade;
use Overblog\GraphQLBundle\Relay\Connection\ConnectionBuilder;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\ClassExtension\ExtendedClassNameResolver;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\ProductIndex;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ProductListItemsCountTest extends GraphQlTestCase
{
    private const array PRODUCT_IDS = [49, 33];

    /**
     * @inject
     */
    private ProductFacade $productFacade;

    /**
     * @inject
     */
    private ProductDataFactory $productDataFactory;

    public function testRepeatedMembershipSnapshotsExceedTheQueryBudget(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListRepeatedMembershipQuery.graphql', [
            'uuid' => Uuid::uuid4()->toString(),
        ]);

        $errors = $this->getErrorsFromResponse($response);
        $this->assertSame('Max query complexity should be 1700 but got 1801.', $errors[0]['message']);
        $this->assertArrayNotHasKey('data', $response);
    }

    public function testHiddenProductIsExcludedFromListAndCount(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);

        $this->hideProductOnCurrentDomain(self::PRODUCT_IDS[0]);

        $this->assertProductListCountMatchesProducts($productListType, $productListUuid, [self::PRODUCT_IDS[1]]);
    }

    public function testVisibleSellingDeniedProductRemainsInListAndCount(): void
    {
        $productListType = ProductListTypeEnum::WISHLIST;
        $productListUuid = $this->createProductList($productListType);
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_IDS[0], Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainSellingDenied[$this->domain->getId()] = true;
        $this->editProductAndRefreshIndex($product, $productData);

        $data = $this->assertProductListCountMatchesProducts($productListType, $productListUuid, self::PRODUCT_IDS);

        $this->assertTrue($data['products'][0]['isSellingDenied']);
    }

    public function testProductPagesPreserveOrderMembershipAndVisibleTotal(): void
    {
        $uuid = $this->createProductList(ProductListTypeEnum::WISHLIST);
        $productUuids = array_map(
            fn (int $id): string => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $id, Product::class)->getUuid(),
            self::PRODUCT_IDS,
        );

        foreach ([0 => [self::PRODUCT_IDS[0]], 1 => [self::PRODUCT_IDS[1]], 2 => []] as $offset => $expectedIds) {
            $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListPageQuery.graphql', [
                'uuid' => $uuid,
                'type' => ProductListTypeEnum::WISHLIST,
                'after' => $offset === 0 ? null : (new ConnectionBuilder())->offsetToCursor($offset - 1),
                'first' => 1,
            ]);
            $data = $this->getResponseDataForGraphQlType($response, 'productList');

            $this->assertSame($expectedIds, array_column(array_column($data['productsConnection']['edges'], 'node'), 'id'));
            $this->assertSame($productUuids, $data['productUuids']);
            $this->assertSame(2, $data['itemsCount']);
            $this->assertSame(2, $data['productsConnection']['totalCount']);
            $this->assertSame($offset === 0, $data['productsConnection']['pageInfo']['hasNextPage']);
        }
    }

    public function testProductPagesFilterVisibilityBeforeApplyingOffset(): void
    {
        $uuid = $this->createProductList(ProductListTypeEnum::WISHLIST);
        $this->hideProductOnCurrentDomain(self::PRODUCT_IDS[0]);
        $visibleProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . self::PRODUCT_IDS[1], Product::class);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListPageQuery.graphql', [
            'uuid' => $uuid,
            'type' => ProductListTypeEnum::WISHLIST,
            'after' => null,
            'first' => 1,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'productList');

        $this->assertSame([self::PRODUCT_IDS[1]], array_column(array_column($data['productsConnection']['edges'], 'node'), 'id'));
        $this->assertSame([$visibleProduct->getUuid()], $data['productUuids']);
        $this->assertSame(1, $data['itemsCount']);
    }

    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testSequentialMutationsReturnFreshListMetadataAndProducts(string $productListType): void
    {
        $uuid = $this->createProductList($productListType);
        $productUuidsById = [];

        foreach ([49, 33, 69] as $id) {
            $productUuidsById[$id] = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $id, Product::class)->getUuid();
        }

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListSequentialMutations.graphql', [
            'uuid' => $uuid,
            'type' => $productListType,
            'firstProductUuid' => $productUuidsById[49],
            'secondProductUuid' => $productUuidsById[33],
            'addedProductUuid' => $productUuidsById[69],
        ]);

        foreach ([
            'added' => [69, 49, 33],
            'moved' => [49, 33, 69],
            'removed' => [49, 33],
            'removedFirst' => [33],
            'recreated' => [69],
            'afterClear' => [33],
        ] as $field => $expectedIds) {
            $data = $this->getResponseDataForGraphQlType($response, $field);
            $this->assertSame($uuid, $data['uuid']);
            $this->assertSame(count($expectedIds), $data['itemsCount']);
            $this->assertSame(array_map(static fn (int $id): string => $productUuidsById[$id], $expectedIds), $data['productUuids']);
            $this->assertSame($expectedIds, array_column($data['products'], 'id'));
            $this->assertSame(array_slice($expectedIds, 0, 2), array_column(array_column($data['productsConnection']['edges'], 'node'), 'id'));
            $this->assertSame(count($expectedIds), $data['productsConnection']['totalCount']);
        }

        $this->assertNull($response['data']['removedLast']);
        $this->assertNull($response['data']['cleared']);
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

    private function hideProductOnCurrentDomain(int $productId): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);
        $productData = $this->productDataFactory->createFromProduct($product);
        $productData->domainHidden[$this->domain->getId()] = true;
        $this->editProductAndRefreshIndex($product, $productData);
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

        $this->assertSame($expectedProductIds, array_column($data['products'], 'id'));
        $this->assertSame(count($expectedProductIds), $data['itemsCount']);

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
