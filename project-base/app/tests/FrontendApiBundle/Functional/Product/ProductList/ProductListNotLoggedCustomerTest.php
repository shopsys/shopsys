<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\ProductListDataFixture;
use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserFacade;
use App\Model\Product\Product;
use Iterator;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Product\List\Exception\UnknownProductListTypeException;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListFacade;
use Shopsys\FrameworkBundle\Model\Product\List\ProductListTypeEnum;
use Shopsys\FrontendApiBundle\Model\Mutation\ProductList\Exception\ProductListUserErrorCodeHelper;
use Tests\FrontendApiBundle\Functional\Customer\User\RegisterTest;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductListNotLoggedCustomerTest extends GraphQlTestCase
{
    /**
     * @inject
     */
    private ProductListFacade $productListFacade;

    /**
     * @inject
     */
    private CustomerUserFacade $customerUserFacade;

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testFindNotExistingProductList(string $productListType): void
    {
        $notExistingUuid = '00000000-0000-0000-0000-000000000000';
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => $notExistingUuid,
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testFindProductListByTypeAndUuidOfAnotherCustomerUserReturnsNull(
        string $productListType,
    ): void {
        $uuidOfAnotherCustomerUser = match ($productListType) {
            ProductListTypeEnum::COMPARISON => ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID,
            ProductListTypeEnum::WISHLIST => ProductListDataFixture::PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID,
            default => throw new UnknownProductListTypeException($productListType),
        };
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => $uuidOfAnotherCustomerUser,
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['productList']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testUserErrorWhenUuidIsNotProvided(string $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => null,
            'type' => $productListType,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame('invalid-find-criteria-for-product-list', $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $uuid
     * @param int[] $expectedProductIds
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
    public function testUserErrorWhenAccessingListsByType(string $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListsByTypeQuery.graphql', [
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame('customer-user-not-logged', $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $productListUuid
     * @param array $expectedProductIds
     */
    public function testAddNewProductToExistingList(
        string $productListType,
        string $productListUuid,
        array $expectedProductIds,
    ): void {
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        array_unshift($expectedProductIds, $productToAddId);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertSame($productListUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame($expectedProductIds, array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
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
    public function testAddProductCreatesNewListWithNewUuidWhenUuidOfCustomerUserListIsProvided(
        string $productListType,
    ): void {
        $customerUserProductListUuid = $this->getCustomerUserProductListUuid($productListType);
        $productToAddId = 69;
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $customerUserProductListUuid,
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'AddProductToList');

        $this->assertNotSame($customerUserProductListUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$productToAddId], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
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
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $uuid
     * @param array $expectedProductIds
     * @throws \JsonException
     */
    public function testProductAlreadyInListUserError(
        string $productListType,
        string $uuid,
        array $expectedProductIds,
    ): void {
        $productToAddId = $expectedProductIds[0];
        $productToAdd = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productToAddId, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $uuid,
            'productUuid' => $productToAdd->getUuid(),
            'type' => $productListType,
        ]);
        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, 'product-already-in-list'), $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $uuid
     */
    public function testRemoveProductFromListProductNotFoundUserError(
        string $productListType,
        string $uuid,
    ): void {
        $notExistingProductUuid = Uuid::uuid4()->toString();
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => $uuid,
            'productUuid' => $notExistingProductUuid,
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame('product-not-found', $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $uuid
     */
    public function testRemoveProductFromListProductNotInListUserError(
        string $productListType,
        string $uuid,
    ): void {
        $productThatIsNotInList = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 69, Product::class);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => $uuid,
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
    public function testRemoveProductFromListProductListNotFoundUserError(string $productListType): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => Uuid::uuid4()->toString(),
            'productUuid' => Uuid::uuid4()->toString(),
            'type' => $productListType,
        ]);

        $this->assertResponseContainsArrayOfErrors($response);
        $errors = $this->getErrorsFromResponse($response);
        $this->assertCount(1, $errors);
        $this->assertSame(ProductListUserErrorCodeHelper::getUserErrorCode($productListType, 'product-list-not-found'), $errors[0]['extensions']['userCode']);
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testRemoveProductFromList(string $productListType): void
    {
        $productListUuid = Uuid::uuid4()->toString();
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2, Product::class);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $product1->getUuid(),
            'type' => $productListType,
        ]);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $product2->getUuid(),
            'type' => $productListType,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $product2->getUuid(),
            'type' => $productListType,
        ]);
        $data = $this->getResponseDataForGraphQlType($response, 'RemoveProductFromList');

        $this->assertSame($productListUuid, $data['uuid']);
        $this->assertSame($productListType, $data['type']);
        $this->assertSame([$product1->getId()], array_column($data['products'], 'id'));
    }

    /**
     * @dataProvider \Tests\FrontendApiBundle\Functional\Product\ProductList\ProductListTypesDataProvider::getProductListTypes
     * @param string $productListType
     */
    public function testRemoveLastProductFromList(string $productListType): void
    {
        $productListUuid = Uuid::uuid4()->toString();
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $product->getUuid(),
            'type' => $productListType,
        ]);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => $productListUuid,
            'productUuid' => $product->getUuid(),
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['RemoveProductFromList']);
    }

    /**
     * @dataProvider productListByTypeAndUuidProvider
     * @param string $productListType
     * @param string $uuid
     */
    public function testRemoveProductList(string $productListType, string $uuid): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductListMutation.graphql', [
            'productListUuid' => $uuid,
            'type' => $productListType,
        ]);

        $this->assertNull($response['data']['RemoveProductList']);
    }

    public function testMergeListsAfterLoginAsCustomerUserWithExistingProductLists(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1, CustomerUser::class);
        $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', [
            'email' => $customerUser->getEmail(),
            'password' => 'user123',
            'productListsUuids' => [
                ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
                ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
            ],
        ]);

        $this->assertOriginalAnonymousListsDoNotExist();

        $this->assertMergedListsOfCustomerUser(
            $customerUser,
            [33, 1],
            [3, 2, 49, 5],
            ProductListDataFixture::PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID,
            ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID,
        );
    }

    public function testMergeListsAfterLoginAsCustomerUserWithoutProductLists(): void
    {
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 2, CustomerUser::class);
        $this->getResponseContentForGql(__DIR__ . '/graphql/LoginMutation.graphql', [
            'email' => $customerUser->getEmail(),
            'password' => 'no-reply.3',
            'productListsUuids' => [
                ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
                ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
            ],
        ]);

        $this->assertMergedListsOfCustomerUser(
            $customerUser,
            [33],
            [3, 2],
            ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
        );
    }

    public function testMergeListsAfterRegistration(): void
    {
        $registerQueryVariables = RegisterTest::getRegisterQueryVariables();
        $registerQueryVariables['productListsUuids'] = [
            ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
        ];
        $this->getResponseContentForGql(__DIR__ . '/../../_graphql/mutation/RegistrationMutation.graphql', $registerQueryVariables);
        $newRegisteredUser = $this->customerUserFacade->findCustomerUserByEmailAndDomain(
            $registerQueryVariables['email'],
            $this->domain->getId(),
        );

        $this->assertOriginalAnonymousListsDoNotExist();

        $this->assertMergedListsOfCustomerUser(
            $newRegisteredUser,
            [33],
            [3, 2],
            ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
        );
    }

    /**
     * @param \App\Model\Customer\User\CustomerUser $customerUser
     * @param int[] $expectedMergedWishlistProductIds
     * @param int[] $expectedMergedComparisonProductIds
     * @param string $expectedMergedWishlistUuid
     * @param string $expectedMergedComparisonUuid
     */
    private function assertMergedListsOfCustomerUser(
        CustomerUser $customerUser,
        array $expectedMergedWishlistProductIds,
        array $expectedMergedComparisonProductIds,
        string $expectedMergedWishlistUuid,
        string $expectedMergedComparisonUuid,
    ): void {
        $currentLoggedCustomerWishlist = $this->productListFacade->findProductListByTypeAndCustomerUser(
            ProductListTypeEnum::WISHLIST,
            $customerUser,
        );
        $currentLoggedCustomerComparison = $this->productListFacade->findProductListByTypeAndCustomerUser(
            ProductListTypeEnum::COMPARISON,
            $customerUser,
        );
        $currentLoggedCustomerWishlistProductIds = $this->productListFacade->getProductIdsByProductList($currentLoggedCustomerWishlist);
        $currentLoggedCustomerComparisonProductIds = $this->productListFacade->getProductIdsByProductList($currentLoggedCustomerComparison);

        $this->assertSame($expectedMergedWishlistProductIds, $currentLoggedCustomerWishlistProductIds);
        $this->assertSame($expectedMergedWishlistUuid, $currentLoggedCustomerWishlist->getUuid());
        $this->assertSame($expectedMergedComparisonProductIds, $currentLoggedCustomerComparisonProductIds);
        $this->assertSame($expectedMergedComparisonUuid, $currentLoggedCustomerComparison->getUuid());
    }

    /**
     * @return \Iterator
     */
    public function productListByTypeAndUuidProvider(): Iterator
    {
        yield [
            'productListType' => ProductListTypeEnum::COMPARISON,
            'uuid' => ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID,
            'expectedProductIds' => [3, 2],
        ];

        yield [
            'productListType' => ProductListTypeEnum::WISHLIST,
            'uuid' => ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID,
            'expectedProductIds' => [33],
        ];
    }

    private function assertOriginalAnonymousListsDoNotExist(): void
    {
        $originalAnonymousWishlist = $this->productListFacade->findAnonymousProductList(ProductListDataFixture::PRODUCT_LIST_WISHLIST_NOT_LOGGED_CUSTOMER_UUID, ProductListTypeEnum::WISHLIST);
        $originalAnonymousComparison = $this->productListFacade->findAnonymousProductList(ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID, ProductListTypeEnum::COMPARISON);

        $this->assertTrue($originalAnonymousWishlist === null, 'Original anonymous wishlist should not exist anymore');
        $this->assertTrue($originalAnonymousComparison === null, 'Original anonymous comparison should not exist anymore');
    }

    /**
     * @param string $productListType
     * @return string
     */
    private function getCustomerUserProductListUuid(string $productListType): string
    {
        return match ($productListType) {
            ProductListTypeEnum::COMPARISON => ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID,
            ProductListTypeEnum::WISHLIST => ProductListDataFixture::PRODUCT_LIST_WISHLIST_LOGGED_CUSTOMER_UUID,
            default => throw new UnknownProductListTypeException($productListType),
        };
    }
}
