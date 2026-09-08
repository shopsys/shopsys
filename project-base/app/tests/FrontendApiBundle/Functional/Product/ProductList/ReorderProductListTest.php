<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\ProductListDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class ReorderProductListTest extends GraphQlTestCase
{
    public function testOrderIsReturnedByMutationAndSubsequentQuery(): void
    {
        $uuid = ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID;
        $response = $this->reorder($uuid, [2, 3]);
        $data = $this->getResponseDataForGraphQlType($response, 'ReorderProductList');
        $this->assertSame([2, 3], array_column($data['products'], 'id'));

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', ['uuid' => $uuid, 'type' => 'COMPARISON']);
        $data = $this->getResponseDataForGraphQlType($response, 'productList');
        $this->assertSame([2, 3], array_column($data['products'], 'id'));
    }

    public function testRejectsDuplicateProducts(): void
    {
        $response = $this->reorder(ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID, [2, 2]);
        $this->assertUserError($response, 'COMPARISON-invalid-product-list-order');
    }

    public function testRejectsProductOutsideTheList(): void
    {
        $response = $this->reorder(ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID, [69]);
        $this->assertUserError($response, 'COMPARISON-invalid-product-list-order');
    }

    public function testCannotReorderCustomerListAnonymously(): void
    {
        $response = $this->reorder(ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID, [2]);
        $this->assertUserError($response, 'COMPARISON-product-list-not-found');
    }

    public function testPartialOrderKeepsOmittedProductsAndAddingStillPrepends(): void
    {
        $uuid = ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID;
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => $uuid,
            'productUuid' => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 69, Product::class)->getUuid(),
            'type' => 'COMPARISON',
        ]);
        $response = $this->reorder($uuid, [2, 69]);
        $data = $this->getResponseDataForGraphQlType($response, 'ReorderProductList');
        $this->assertSame([2, 3, 69], array_column($data['products'], 'id'));
    }

    /**
     * @param int[] $productIds
     */
    private function reorder(string $uuid, array $productIds): array
    {
        $productUuids = array_map(fn (int $id) => $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $id, Product::class)->getUuid(), $productIds);

        return $this->getResponseContentForGql(__DIR__ . '/graphql/ReorderProductListMutation.graphql', [
            'uuid' => $uuid,
            'type' => 'COMPARISON',
            'productUuids' => $productUuids,
        ]);
    }
}
