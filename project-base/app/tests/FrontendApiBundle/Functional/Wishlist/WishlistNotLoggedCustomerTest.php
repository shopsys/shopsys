<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Wishlist;

use App\DataFixtures\Demo\ProductDataFixture;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class WishlistNotLoggedCustomerTest extends GraphQlTestCase
{
    public function testAddProductsAsNotLoggedCustomer(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->getResponseDataForGraphQlType($response1, 'addProductToWishlist');
        $this->assertArrayHasKey('uuid', $response1['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToWishlist']);
        $this->assertSame(1, count($response1['data']['addProductToWishlist']['products']));
        $wishlistUuid = $response1['data']['addProductToWishlist']['uuid'];

        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product2->getUuid(), 'wishlistUuid' => $wishlistUuid]);

        $this->getResponseDataForGraphQlType($response2, 'addProductToWishlist');
        $this->assertArrayHasKey('uuid', $response2['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response2['data']['addProductToWishlist']);
        $this->assertSame(2, count($response2['data']['addProductToWishlist']['products']));
        $this->assertSame($wishlistUuid, $response2['data']['addProductToWishlist']['uuid']);
    }
}
