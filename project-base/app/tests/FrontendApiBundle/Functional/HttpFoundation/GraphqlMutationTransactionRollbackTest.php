<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\HttpFoundation;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class GraphqlMutationTransactionRollbackTest extends GraphQlTestCase
{
    public function testMutationWithGraphqlErrorRollsBackPreviousWrite(): void
    {
        $this->runWithIsolatedProductList([$this, 'doTestMutationWithGraphqlErrorRollsBackPreviousWrite']);
    }

    public function testSuccessfulMutationCommitsChanges(): void
    {
        $this->runWithIsolatedProductList([$this, 'doTestSuccessfulMutationCommitsChanges']);
    }

    public function testQueryErrorDoesNotAffectFollowingMutationCommit(): void
    {
        $this->runWithIsolatedProductList([$this, 'doTestQueryErrorDoesNotAffectFollowingMutationCommit']);
    }

    private function doTestMutationWithGraphqlErrorRollsBackPreviousWrite(string $productListUuid): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/AddProductToListAndFailMutation.graphql',
            [
                'productUuid' => $this->getProductUuid(69),
                'notExistingProductUuid' => Uuid::uuid4()->toString(),
                'productListUuid' => $productListUuid,
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );

        $this->assertUserError($response, 'product-not-found');
        $this->assertSame([69], array_column($response['data']['AddProductToList']['products'], 'id'));
        $this->assertNull($this->findProductListByUuid($productListUuid));
    }

    private function doTestSuccessfulMutationCommitsChanges(string $productListUuid): void
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../Product/ProductList/graphql/AddProductToListMutation.graphql',
            [
                'productUuid' => $this->getProductUuid(69),
                'productListUuid' => $productListUuid,
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );
        $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $productList = $this->findProductListByUuid($productListUuid);

        $this->assertNotNull($productList);
        $this->assertSame([69], array_column($productList['products'], 'id'));
    }

    private function doTestQueryErrorDoesNotAffectFollowingMutationCommit(string $productListUuid): void
    {
        $queryResponse = $this->getResponseContentForGql(
            __DIR__ . '/graphql/InvalidProductListQuery.graphql',
            [
                'type' => ProductListTypeEnum::WISHLIST,
                'productListUuid' => $productListUuid,
            ],
        );
        $this->assertResponseContainsArrayOfErrors($queryResponse);

        $mutationResponse = $this->getResponseContentForGql(
            __DIR__ . '/../Product/ProductList/graphql/AddProductToListMutation.graphql',
            [
                'productUuid' => $this->getProductUuid(69),
                'productListUuid' => $productListUuid,
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );
        $this->getResponseDataForGraphQlType($mutationResponse, 'AddProductToList');

        $productList = $this->findProductListByUuid($productListUuid);

        $this->assertNotNull($productList);
        $this->assertSame([69], array_column($productList['products'], 'id'));
    }

    /**
     * @param callable(string): void $scenario
     */
    private function runWithIsolatedProductList(callable $scenario): void
    {
        $productListUuid = Uuid::uuid4()->toString();

        $this->withIsolatedClient(
            fn () => $scenario($productListUuid),
            fn () => $this->removeProductListIfExists($productListUuid),
        );
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function findProductListByUuid(string $productListUuid): ?array
    {
        $response = $this->getResponseContentForGql(
            __DIR__ . '/../Product/ProductList/graphql/ProductListQuery.graphql',
            [
                'uuid' => $productListUuid,
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );

        return $response['data']['productList'];
    }

    protected function removeProductListIfExists(string $productListUuid): void
    {
        if ($this->findProductListByUuid($productListUuid) === null) {
            return;
        }

        $this->getResponseContentForGql(
            __DIR__ . '/../Product/ProductList/graphql/RemoveProductListMutation.graphql',
            [
                'productListUuid' => $productListUuid,
                'type' => ProductListTypeEnum::WISHLIST,
            ],
        );
    }

    protected function getProductUuid(int $productId): string
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class);

        return $product->getUuid();
    }
}
