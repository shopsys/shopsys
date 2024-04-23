<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\ProductListDataFixture;
use App\Model\Product\Product;
use Iterator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\UnknownProductListTypeException;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListUserErrorCodeHelper;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ProductListLoggedCustomerTest extends GraphQlWithLoginTestCase
{
    /**
     * @dataProvider productListDataProvider
     * @param string $productListType
     * @param string $expectedUuid
     * @param string[] $expectedProductIds
     */
    public function testFindProductListByType(
        string $productListType,
        string $expectedUuid,
        array $expectedProductIds,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'productList');

        $this->assertSame($expectedUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame($expectedProductIds, array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider productListDataProvider
     * @param string $productListType
     * @param string $uuid
     * @param string[] $expectedProductIds
     */
    public function testFindProductListByTypeAndUuid(
        string $productListType,
        string $uuid,
        array $expectedProductIds,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => $uuid,
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'productList');

        $this->assertSame($uuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame($expectedProductIds, array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testFindProductListByTypeAndUuidOfAnotherCustomerUserReturnsNull(
        string $productListType,
    ): void {
        $uuidOfAnotherCustomerUser = match ($productListType) {
            ProductListTypeEnum::COMPARISON => ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
            ProductListTypeEnum::WISHLIST => ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            default => throw new UnknownProductListTypeException($productListType),
        };
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => $uuidOfAnotherCustomerUser,
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @dataProvider productListDataProvider
     * @param string $productListType
     * @param string $uuid
     * @param int[] $expectedProductIds
     */
    public function testGetListsByType(
        string $productListType,
        string $uuid,
        array $expectedProductIds,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListsByTypeQuery.graphql', [
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'productListsByType');

        $this->assertIsArray($data);
        $this->assertCount(1, $data);
        $productListData = $data[0];
        $this->assertSame($uuid, $productListData['uuid']);
        $this->assertSame($productListType, $productListData['type']);
        $this->assertSame($expectedProductIds, array_column($productListData['products'], 'id'));
    }

    /**
     * @dataProvider productListDataProvider
     * @param string $productListType
     * @param string $expectedUuid
     * @param array $expectedProductIds
     * @throws \JsonException
     */
    public function testAddNewProductToExistingList(
        string $productListType,
        string $expectedUuid,
        array $expectedProductIds,
    ): void {
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        array_unshift($expectedProductIds, $productToAddId);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertSame($expectedUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame($expectedProductIds, array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     * @throws \JsonException
     */
    public function testAddProductCreatesNewListWhenNewUuidIsProvided(string $productListType): void
    {
        $newUuid = Uuid::uuid4()->toString();
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $newUuid,
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertSame($newUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$productToAddId], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testAddProductCreatesNewListWithNewUuidWhenUuidOfAnonymousListIsProvided(
        string $productListType,
    ): void {
        $anonymousProductListUuid = $this->getAnonymousProductListUuid($productListType);
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $anonymousProductListUuid,
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertNotSame($anonymousProductListUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$productToAddId], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider productListDataProvider
     * @param string $productListType
     * @param string $uuid
     * @param array $expectedProductIds
     */
    public function testProductAlreadyInListUserError(
        string $productListType,
        string $uuid,
        array $expectedProductIds,
    ): void {
        $productToAddId = $expectedProductIds[0];
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, 'product-already-in-list'), $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testRemoveProductFromListProductNotFoundUserError(string $productListType): void
    {
        $notExistingProductUuid = Uuid::uuid4()->toString();
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $notExistingProductUuid,
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame('product-not-found', $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     * @throws \JsonException
     */
    public function testRemoveProductFromListProductNotInListUserError(string $productListType): void
    {
        $productThatIsNotInList = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 69, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $productThatIsNotInList->getUuid(),
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, 'product-not-in-list'), $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testRemoveProductList(string $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductListMutation.graphql', [
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['RemoveProductList']);
    }

    /**
     * @return \Iterator
     */
    public function productListDataProvider(): Iterator
    {
        yield [
            'productListType' => ProductListTypeEnum::COMPARISON,
            'expectedUuid' => ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID,
            'expectedProductIds' => [49, 5],
        ];

        yield [
            'productListType' => ProductListTypeEnum::WISHLIST,
            'expectedUuid' => ProductListDataFixture::PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID,
            'expectedProductIds' => [1],
        ];
    }

    /**
     * @param string $productListType
     * @return string
     */
    private function getAnonymousProductListUuid(string $productListType): string
    {
        return match ($productListType) {
            ProductListTypeEnum::COMPARISON => ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
            ProductListTypeEnum::WISHLIST => ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            default => throw new UnknownProductListTypeException($productListType),
        };
    }
}
