<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListNotFoundUserError;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListUserErrorCodeHelper;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class ProductListLoggedCustomerWithoutListTest extends GraphQlWithLoginTestCase
{
    public const DEFAULT_USER_EMAIL = 'no-reply.3@shopsys.com';
    public const DEFAULT_USER_PASSWORD = 'no-reply.3';

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testFindProductListForCustomerUserWithoutProductListReturnsNull(
        ProductListTypeEnum $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'type' => $productListType->name,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testGetProductListsForCustomerUserWithoutProductListReturnsEmptyArray(
        ProductListTypeEnum $productListType,
    ): void {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListsByTypeQuery.graphql', [
            'type' => $productListType->name,
        ]);

        $this->assertEmpty($response['data']['productListsByType']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testAddProductCreatesNewList(ProductListTypeEnum $productListType): void
    {
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType->name,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertSame($productListType->name, $data['type']);
        $this->assertSame([$productToAddId], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testRemoveProductFromListProductListNotFoundUserError(ProductListTypeEnum $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => Uuid::uuid4()->toString(),
            'type' => $productListType->name,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, ProductListNotFoundUserError::CODE), $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testRemoveProductFromList(ProductListTypeEnum $productListType): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $addProductResponse = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product1->getUuid(),
            'type' => $productListType->name,
        ]);
        $productListUuid = $this->getResponseDataForGraphQlType($addProductResponse, 'AddProductToList')['uuid'];
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product2->getUuid(),
            'type' => $productListType->name,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $product2->getUuid(),
            'type' => $productListType->name,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'RemoveProductFromList');

        $this->assertSame($productListUuid, $data['uuid']);
        $this->assertSame($productListType->name, $data['type']);
        $this->assertSame([$product1->getId()], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param \Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum $productListType
     */
    public function testRemoveLastProductFromList(ProductListTypeEnum $productListType): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'type' => $productListType->name,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'type' => $productListType->name,
        ]);

        $this->assertNull($response['data']['RemoveProductFromList']);
    }
}
