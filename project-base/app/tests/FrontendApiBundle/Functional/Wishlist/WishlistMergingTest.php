<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Wishlist;

use App\DataFixtures\Demo\CustomerUserDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\FrontendApi\Model\Wishlist\WishlistFacade;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class WishlistMergingTest extends GraphQlWithLoginTestCase
{
    /**
     * @inject
     */
    private WishlistFacade $wishlistFacade;

    public function testTransformNotLoggedCustomerWishlistToLoggedCustomerWishlist(): void
    {
        $this->logout();
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->getResponseDataForGraphQlType($response1, 'addProductToWishlist');

        $this->assertArrayHasKey('uuid', $response1['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToWishlist']);
        $this->assertSame(1, count($response1['data']['addProductToWishlist']['products']));
        $wishlistUuid = $response1['data']['addProductToWishlist']['uuid'];

        $this->login();
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql', ['wishlistUuid' => $wishlistUuid]);

        $this->getResponseDataForGraphQlType($response2, 'wishlist');

        $this->assertArrayHasKey('uuid', $response2['data']['wishlist']);
        $this->assertArrayHasKey('products', $response2['data']['wishlist']);
        $this->assertSame(1, count($response2['data']['wishlist']['products']));
        $this->assertSame($wishlistUuid, $response2['data']['wishlist']['uuid']);
        $this->assertSame($response1['data']['addProductToWishlist']['products'], $response2['data']['wishlist']['products']);

        /** @var \App\Model\Customer\User\CustomerUser $customerUser */
        $customerUser = $this->getReference(CustomerUserDataFixture::CUSTOMER_PREFIX . 1);
        $loggedWishlist = $this->wishlistFacade->getWishlistOfCustomerUser($customerUser);
        $this->assertNotNull($loggedWishlist);
        $this->assertSame($response2['data']['wishlist']['products'][0]['name'], $loggedWishlist->getItems()[0]->getProduct()->getName());
    }

    public function testMergeNotLoggedCustomerWishlistWithLoggedCustomerWishlist(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->getResponseDataForGraphQlType($response1, 'addProductToWishlist');

        $this->assertArrayHasKey('uuid', $response1['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToWishlist']);
        $this->assertSame(1, count($response1['data']['addProductToWishlist']['products']));
        $wishlistUuidOfLoggedUser = $response1['data']['addProductToWishlist']['uuid'];

        $this->logout();
        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $response2 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product2->getUuid()]);

        $this->getResponseDataForGraphQlType($response2, 'addProductToWishlist');

        $this->assertArrayHasKey('uuid', $response2['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response2['data']['addProductToWishlist']);
        $this->assertSame(1, count($response2['data']['addProductToWishlist']['products']));
        $this->assertNotSame($wishlistUuidOfLoggedUser, $response2['data']['addProductToWishlist']['uuid']);
        $wishlistUuidOfNotLoggedUser = $response2['data']['addProductToWishlist']['uuid'];

        $this->login();
        $response3 = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql', ['wishlistUuid' => $wishlistUuidOfNotLoggedUser]);

        $this->getResponseDataForGraphQlType($response3, 'wishlist');
        $this->assertArrayHasKey('uuid', $response3['data']['wishlist']);
        $this->assertArrayHasKey('products', $response3['data']['wishlist']);
        $this->assertSame(2, count($response3['data']['wishlist']['products']));
        $this->assertSame($wishlistUuidOfLoggedUser, $response3['data']['wishlist']['uuid']);

        $this->logout();
        $response4 = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql', ['wishlistUuid' => $wishlistUuidOfNotLoggedUser]);
        $this->assertArrayHasKey('data', $response4);
        $this->assertArrayHasKey('wishlist', $response4['data']);
        $this->assertNull($response4['data']['wishlist']);
    }

    public function testLogoutCustomerWithWishlist(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response1 = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->getResponseDataForGraphQlType($response1, 'addProductToWishlist');
        $this->assertArrayHasKey('uuid', $response1['data']['addProductToWishlist']);
        $this->assertArrayHasKey('products', $response1['data']['addProductToWishlist']);
        $this->assertSame(1, count($response1['data']['addProductToWishlist']['products']));

        $this->logout();
        $response4 = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql');
        $this->assertArrayHasKey('data', $response4);
        $this->assertArrayHasKey('wishlist', $response4['data']);
        $this->assertNull($response4['data']['wishlist']);
    }
}
