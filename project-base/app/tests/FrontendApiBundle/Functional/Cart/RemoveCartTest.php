<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemoveCartTest extends GraphQlTestCase
{
    private Product $testingProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1, Product::class);
    }

    public function testCartIsRemoved(): void
    {
        $firstProductQuantity = 6;
        $newlyCreatedCart = $this->addTestingProductToNewCart($firstProductQuantity);
        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72, Product::class);
        $secondProductQuantity = 3;

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $secondProductQuantity,
        ]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
        ]);

        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('RemoveCart', $response['data']);
        $isCartRemoved = $response['data']['RemoveCart'];
        $this->assertTrue($isCartRemoved);

        $response = $this->getResponseContentForGql(
            __DIR__ . '/graphql/GetCart.graphql',
            ['cartUuid' => $newlyCreatedCart['uuid']],
        );

        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        self::assertEmpty($data['items']);
    }

    /**
     * @param int $productQuantity
     * @return array
     */
    private function addTestingProductToNewCart(int $productQuantity): array
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $this->testingProduct->getUuid(),
            'quantity' => $productQuantity,
        ]);

        return $response['data']['AddToCart']['cart'];
    }
}
