<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Wishlist;

use App\DataFixtures\Demo\ProductDataFixture;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\FrontendApiBundle\Test\GraphQlWithLoginTestCase;

class WishlistLoggedCustomerTest extends GraphQlWithLoginTestCase
{
    public function testGetNotExistsWishlist(): void
    {
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql');

        $this->assertNull($response['data']['wishlist']);
    }

    public function testGetWishlistOfLoggedCustomer(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $responseExpected = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/WishlistQuery.graphql');

        $this->getResponseDataForGraphQlType($response, 'wishlist');

        $this->assertArrayHasKey('products', $response['data']['wishlist']);
        $this->assertSame($responseExpected['data']['addProductToWishlist']['products'], $response['data']['wishlist']['products']);
    }

    public function testAddProductToWishlist(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product->getUuid()]);

        $this->getResponseDataForGraphQlType($response, 'addProductToWishlist');

        $this->assertArrayHasKey('products', $response['data']['addProductToWishlist']);
        $this->assertArrayHasKey(0, $response['data']['addProductToWishlist']['products']);
        $this->assertArrayHasKey('name', $response['data']['addProductToWishlist']['products'][0]);
        $this->assertSame(t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $response['data']['addProductToWishlist']['products'][0]['name']);
    }

    public function testAddSameProductToWishlist(): void
    {
        /** @var \App\Model\Product\Product $product */
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product->getUuid()]);

        $expected = 'Product ' . t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()) . ' in wishlist already exists.';
        $this->assertSame($expected, $response['errors'][0]['message']);
    }

    public function testRemoveProductFromWishlist(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        /** @var \App\Model\Product\Product $product2 */
        $product2 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 2);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product2->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertSame(1, count($response['data']['removeProductFromWishlist']['products'][0]));
        $this->assertSame(t('32" Philips 32PFL4308', [], Translator::DATA_FIXTURES_TRANSLATION_DOMAIN, $this->getLocaleForFirstDomain()), $response['data']['removeProductFromWishlist']['products'][0]['name']);
    }

    public function testRemoveLastProductFromWishlist(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/RemoveProductFromWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $this->assertNull($response['data']['removeProductFromWishlist']);
    }

    public function testCleanWishlist(): void
    {
        /** @var \App\Model\Product\Product $product1 */
        $product1 = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . 1);
        $this->getResponseContentForGql(__DIR__ . '/graphql/AddProductToWishlistMutation.graphql', ['productUuid' => $product1->getUuid()]);

        $response = $this->getResponseContentForGql(__DIR__ . '/graphql/CleanWishlistMutation.graphql');

        $this->assertNull($response['data']['cleanWishlist']);
    }
}
