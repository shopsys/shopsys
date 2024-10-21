<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use Ramsey\Uuid\Uuid;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListUserErrorCodeHelper;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ProductListLoggedCustomerWithoutListTest extends GraphQlWithLoginTestCase
{
    public const string DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const string DEFAULT_USER_PASSWORD = 'no-reply.3';

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testFindProductListForCustomerUserWithoutProductListReturnsNull(
        string $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testGetProductListsForCustomerUserWithoutProductListReturnsEmptyArray(
        string $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListsByTypeQuery.graphql', [
            'type' => $productListType,
        ]);

        $this->assertEmpty($response['data']['productListsByType']);
    }

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testAddProductCreatesNewList(string $productListType): void
    {
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$productToAddId], array_column($data['products'], 'id'));
    }

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testRemoveProductFromListProductListNotFoundUserError(string $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => Uuid::uuid4()->toString(),
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, 'product-list-not-found'), $errors[0]['extensions']['userCode']);
    }

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testRemoveProductFromList(string $productListType): void
    {
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);
        $addProductResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product1->getUuid(),
            'type' => $productListType,
        ]);
        $productListUuid = $this->getResponseDataForGraphQlType($addProductResponse, 'AddProductToList')['uuid'];
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product2->getUuid(),
            'type' => $productListType,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $product2->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'RemoveProductFromList');

        $this->assertSame($productListUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$product1->getId()], array_column($data['products'], 'id'));
    }

    /**
     * @param string $productListType
     */
    #[DataProviderExternal(ProductListTypesDataProvider::class, 'getProductListTypes')]
    public function testRemoveLastProductFromList(string $productListType): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'type' => $productListType,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['RemoveProductFromList']);
    }
}
