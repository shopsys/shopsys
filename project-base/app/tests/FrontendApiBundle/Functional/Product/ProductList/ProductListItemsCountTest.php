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
