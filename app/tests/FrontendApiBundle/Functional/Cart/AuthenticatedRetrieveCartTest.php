<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Cart;

use App\DataFixtures\Demo\ProductDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class AuthenticatedRetrieveCartTest extends GraphQlWithLoginTestCase
{
    public function testGetCartWithoutArguments(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);

        $this->getResponseContentForGql(__DIR__ . '/../_graphql/mutation/AddToCartMutation.graphql', [
            'productUuid' => $product->getUuid(),
            'quantity' => 2,
        ]);

        $getCartQuery = '{
            cart {
                uuid
                items {
                    product {
                        uuid
                    }
                    quantity
                }
            }
        }';

        $response = $this->getResponseContentForQuery($getCartQuery);
        $data = $this->getResponseDataForGraphQlType($response, 'cart');

        self::assertNull($data['uuid']);
        self::assertNotEmpty($data['items']);

        self::assertEquals($product->getUuid(), $data['items'][0]['product']['uuid']);
        self::assertEquals(2, $data['items'][0]['quantity']);
    }
}
