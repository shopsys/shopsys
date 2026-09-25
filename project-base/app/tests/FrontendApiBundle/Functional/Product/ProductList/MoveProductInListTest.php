<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product\ProductList;

use App\DataFixtures\Demo\ProductDataFixture;
use App\DataFixtures\Demo\ProductListDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

final class MoveProductInListTest extends GraphQlTestCase
{
    private const string LIST_UUID = ProductListDataFixture::PRODUCT_LIST_COMPARISON_NOT_LOGGED_CUSTOMER_UUID;

    public function testProductMovedAfterAnotherProductKeepsItsPositionInList(): void
    {
        $response = $this->moveProduct(self::LIST_UUID, 3, 2);

        $data = $this->getResponseDataForGraphQlType($response, 'MoveProductInList');
        $this->assertSame([2, 3], array_column($data['products'], 'id'));
        $this->assertSame([2, 3], $this->getListProductIds());
    }

    public function testProductMovedWithoutAfterProductIsPlacedOnTop(): void
    {
        $response = $this->moveProduct(self::LIST_UUID, 2, null);

        $data = $this->getResponseDataForGraphQlType($response, 'MoveProductInList');
        $this->assertSame([2, 3], array_column($data['products'], 'id'));
    }

    public function testAddedProductIsPlacedOnTop(): void
    {
        $this->addProduct(69);

        $this->assertSame([69, 3, 2], $this->getListProductIds());
    }

    public function testProductMovedUpIsPlacedRightAfterAnotherProduct(): void
    {
        $this->addProduct(69);

        $response = $this->moveProduct(self::LIST_UUID, 2, 69);

        $data = $this->getResponseDataForGraphQlType($response, 'MoveProductInList');
        $this->assertSame([69, 2, 3], array_column($data['products'], 'id'));
    }

    public function testProductMovedDownIsPlacedRightAfterAnotherProduct(): void
    {
        $this->addProduct(69);

        $response = $this->moveProduct(self::LIST_UUID, 69, 3);

        $data = $this->getResponseDataForGraphQlType($response, 'MoveProductInList');
        $this->assertSame([3, 69, 2], array_column($data['products'], 'id'));
    }

    public function testProductMovedAfterRemovalIsPlacedRightAfterAnotherProduct(): void
    {
        $this->addProduct(69);
        $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromListMutation.graphql', [
            'productListUuid' => self::LIST_UUID,
            'productUuid' => $this->getProductUuid(3),
            'type' => 'COMPARISON',
        ]);

        $response = $this->moveProduct(self::LIST_UUID, 69, 2);

        $data = $this->getResponseDataForGraphQlType($response, 'MoveProductInList');
        $this->assertSame([2, 69], array_column($data['products'], 'id'));
    }

    public function testMovingProductOutsideTheListIsRejected(): void
    {
        $response = $this->moveProduct(self::LIST_UUID, 69, null);

        $this->assertUserError($response, 'COMPARISON-product-not-in-list');
    }

    public function testMovingProductAfterProductOutsideTheListIsRejected(): void
    {
        $response = $this->moveProduct(self::LIST_UUID, 2, 69);

        $this->assertUserError($response, 'COMPARISON-product-not-in-list');
    }

    public function testProductInCustomerListCannotBeMovedAnonymously(): void
    {
        $response = $this->moveProduct(ProductListDataFixture::PRODUCT_LIST_COMPARISON_LOGGED_CUSTOMER_UUID, 5, null);

        $this->assertUserError($response, 'COMPARISON-product-list-not-found');
    }

    private function moveProduct(string $listUuid, int $productId, ?int $afterProductId): array
    {
        return $this->getResponseContentForGql(__DIR__ . '/graphql/MoveProductInListMutation.graphql', [
            'uuid' => $listUuid,
            'type' => 'COMPARISON',
            'productUuid' => $this->getProductUuid($productId),
            'afterProductUuid' => $afterProductId === null ? null : $this->getProductUuid($afterProductId),
        ]);
    }

    private function addProduct(int $productId): void
    {
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToListMutation.graphql', [
            'productListUuid' => self::LIST_UUID,
            'productUuid' => $this->getProductUuid($productId),
            'type' => 'COMPARISON',
        ]);
    }

    /**
     * @return int[]
     */
    private function getListProductIds(): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/ProductListQuery.graphql', [
            'uuid' => self::LIST_UUID,
            'type' => 'COMPARISON',
        ]);

        return array_column($this->getResponseDataForGraphQlType($response, 'productList')['products'], 'id');
    }

    private function getProductUuid(int $productId): string
    {
        return $this->getReference(ProductDataFixture::PRODUCT_PREFIX . $productId, Product::class)->getUuid();
    }
}
