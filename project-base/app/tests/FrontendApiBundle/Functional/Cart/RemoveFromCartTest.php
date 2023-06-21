<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class RemoveFromCartTest extends GraphQlTestCase
{
    private Product $testingProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
    }

    public function testItemIsRemovedFromCart(): void
    {
        $firstProductQuantity = 6;
        $newlyCreatedCart = $this->addTestingProductToNewCart($firstProductQuantity);
        /** @var \App\Model\Product\Product $secondProduct */
        $secondProduct = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 72);
        $secondProductQuantity = 3;

        $response = $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'cartUuid' => $newlyCreatedCart['uuid'],
            'productUuid' => $secondProduct->getUuid(),
            'quantity' => $secondProductQuantity,
        ]);

        $cartItems = $response['data']['AddToCart']['cart']['items'];
        $firstCartItemUuid = $cartItems[0]['uuid'];
        $secondCartItemUuid = $cartItems[1]['uuid'];

        self::assertCount(2, $cartItems);

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '", 
                cartItemUuid: "' . $secondCartItemUuid . '"
            }) {
                items {
                    quantity
                    product {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($removeFromCartMutation);
        $cartItems = $response['data']['RemoveFromCart']['items'];

        self::assertCount(1, $cartItems);
        self::assertEquals($firstProductQuantity, $cartItems[0]['quantity']);
        self::assertEquals($this->testingProduct->getUuid(), $cartItems[0]['product']['uuid']);

        $removeFromCartMutation = 'mutation {
            RemoveFromCart(input: {
                cartUuid: "' . $newlyCreatedCart['uuid'] . '", 
                cartItemUuid: "' . $firstCartItemUuid . '"
            }) {
                items {
                    quantity
                    product {
                        uuid
                    }
                }
            }
        }';

        $response = $this->getResponseContentForQuery($removeFromCartMutation);
        $cartItems = $response['data']['RemoveFromCart']['items'];

        self::assertEmpty($cartItems);
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
